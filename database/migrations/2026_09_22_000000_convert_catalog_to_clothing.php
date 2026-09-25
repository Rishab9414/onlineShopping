<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->removeObsoleteColumns();
        $this->removeObsoleteTables();

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_key')->default(0)->after('variant_id');
        });

        DB::table('cart_items')->update([
            'variant_key' => DB::raw('COALESCE(variant_id, 0)'),
        ]);

        Schema::table('cart_items', function (Blueprint $table) {
            // MariaDB requires an index to continue backing the user foreign key
            // before the original compound unique index can be removed.
            $table->index('user_id', 'cart_items_user_id_index');
            $table->dropUnique('cart_items_user_id_product_id_unique');
            $table->dropUnique('cart_items_session_id_product_id_unique');
            $table->unique(['user_id', 'product_id', 'variant_key'], 'cart_items_user_product_variant_unique');
            $table->unique(['session_id', 'product_id', 'variant_key'], 'cart_items_session_product_variant_unique');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('color_key')->default(0)->after('color_id');
            $table->unsignedBigInteger('size_key')->default(0)->after('size_id');
            $table->unsignedBigInteger('material_key')->default(0)->after('material_id');
            $table->unsignedInteger('reserved_stock')->default(0)->after('stock');
        });

        DB::table('product_variants')->update([
            'color_key' => DB::raw('COALESCE(color_id, 0)'),
            'size_key' => DB::raw('COALESCE(size_id, 0)'),
            'material_key' => DB::raw('COALESCE(material_id, 0)'),
        ]);

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique(
                ['product_id', 'color_key', 'size_key', 'material_key'],
                'product_variants_product_attributes_unique'
            );
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('variant_sku')->nullable()->after('sku');
            $table->string('size_snapshot')->nullable()->after('variant_sku');
            $table->string('color_snapshot')->nullable()->after('size_snapshot');
            $table->string('material_snapshot')->nullable()->after('color_snapshot');
        });

        Schema::create('size_charts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit', 20)->default('cm');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('size_chart_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_chart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->string('size_label');
            $table->string('measurement');
            $table->decimal('min_value', 8, 2)->nullable();
            $table->decimal('max_value', 8, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(
                ['size_chart_id', 'size_label', 'measurement'],
                'size_chart_measurement_unique'
            );
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('size_chart_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('size_chart_id')->nullable()->constrained()->nullOnDelete();
        });

        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', [
                'shop_by_bike_enabled',
                'delhivery_enabled',
                'delhivery_token',
                'delhivery_client_name',
            ])->delete();
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('size_chart_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('size_chart_id');
        });

        Schema::dropIfExists('size_chart_measurements');
        Schema::dropIfExists('size_charts');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'variant_sku', 'size_snapshot', 'color_snapshot', 'material_snapshot',
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique('product_variants_product_attributes_unique');
            $table->dropColumn(['color_key', 'size_key', 'material_key', 'reserved_stock']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_user_product_variant_unique');
            $table->dropUnique('cart_items_session_product_variant_unique');
            $table->dropColumn('variant_key');
            $table->unique(['user_id', 'product_id']);
            $table->unique(['session_id', 'product_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_user_id_index');
        });
    }

    private function removeObsoleteColumns(): void
    {
        $this->dropForeignColumn('orders', 'shipment_id');
        $this->dropForeignColumn('customers', 'referred_by');

        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'referral_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropUnique('customers_referral_code_unique');
            });
        }

        $this->dropColumns('orders', [
            'wallet_discount',
        ]);
        $this->dropColumns('carts', ['coupon_id']);
        $this->dropColumns('customers', ['referral_code', 'loyalty_tier']);
        $this->dropColumns('categories', ['banner_image']);
        $this->dropColumns('brands', ['banner']);
    }

    private function removeObsoleteTables(): void
    {
        foreach ([
            'shipment_tracking',
            'shipments',
            'wallet_transactions',
            'wallets',
            'loyalty_transactions',
            'loyalty_points',
            'customer_referrals',
            'product_bike_model',
            'bike_models',
            'vehicle_brands',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function dropForeignColumn(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->dropConstrainedForeignId($column);
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existing = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($table, $column)
        ));

        if ($existing !== []) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn($existing));
        }
    }
};
