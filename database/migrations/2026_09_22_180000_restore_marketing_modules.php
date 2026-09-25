<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('image');
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('link_url')->nullable();
                $table->string('button_text')->default('Shop Now');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('text');
                $table->string('icon', 20)->nullable();
                $table->string('link_url')->nullable();
                $table->enum('type', ['promo', 'trust', 'info'])->default('info');
                $table->enum('position', ['top_bar', 'ticker'])->default('ticker');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('blog_posts')) {
            Schema::create('blog_posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('excerpt', 500)->nullable();
                $table->longText('content');
                $table->string('featured_image')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 500)->nullable();
                $table->string('meta_keywords')->nullable();
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->unsignedInteger('views')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('promo_popups')) {
            Schema::create('promo_popups', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subtitle', 500)->nullable();
                $table->string('image');
                $table->string('link_url', 500)->nullable();
                $table->string('button_text', 100)->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('home_reels')) {
            Schema::create('home_reels', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('label')->nullable();
                $table->string('video');
                $table->string('thumbnail')->nullable();
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('link_url')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('home_reels', 'category_id')) {
            Schema::table('home_reels', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('thumbnail')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasTable('home_themes')) {
            Schema::create('home_themes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('preset')->default('default');
                $table->string('primary_color', 7)->default('#761737');
                $table->string('secondary_color', 7)->default('#32161f');
                $table->string('accent_color', 7)->default('#4f1028');
                $table->string('ticker_bg_color', 7)->default('#761737');
                $table->string('decoration')->default('none');
                $table->string('hero_overlay')->default('default');
                $table->string('hero_badge_text')->nullable();
                $table->unsignedSmallInteger('priority')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('description')->nullable();
                $table->enum('type', ['fixed', 'percent'])->default('fixed');
                $table->decimal('value', 12, 2);
                $table->decimal('min_order_amount', 12, 2)->nullable();
                $table->decimal('max_discount', 12, 2)->nullable();
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('usage_per_customer')->nullable()->default(1);
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('discount_amount', 12, 2);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'coupon_id')) {
                    $table->foreignId('coupon_id')->nullable()->after('discount')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('orders', 'coupon_code')) {
                    $table->string('coupon_code')->nullable()->after('coupon_id');
                }
                if (! Schema::hasColumn('orders', 'coupon_discount')) {
                    $table->decimal('coupon_discount', 12, 2)->nullable()->after('coupon_code');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insertOrIgnore([
                ['key' => 'coupons_enabled', 'value' => '1'],
                ['key' => 'home_reels_enabled', 'value' => '1'],
                ['key' => 'home_reels_autoplay', 'value' => '1'],
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'coupon_id')) {
                    $table->dropConstrainedForeignId('coupon_id');
                }
                $columns = array_values(array_filter([
                    Schema::hasColumn('orders', 'coupon_code') ? 'coupon_code' : null,
                    Schema::hasColumn('orders', 'coupon_discount') ? 'coupon_discount' : null,
                ]));
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
