<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Support\StoreProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Storage;

class DocumentPdfService
{
    public function __construct(private StoreProfile $store) {}

    public function invoicePdf(Order $order): DomPdf
    {
        $order->loadMissing(['items', 'customer', 'user', 'invoiceRecord']);

        return $this->makePdf('pdf.invoice', [
            'order' => $order,
            'store' => $this->store,
            'logoSrc' => $this->logoSrc(),
        ]);
    }

    public function packingSlipPdf(Order $order): DomPdf
    {
        $order->loadMissing(['items', 'customer', 'user']);

        return $this->makePdf('pdf.packing-slip', [
            'order' => $order,
            'store' => $this->store,
            'logoSrc' => $this->logoSrc(),
        ]);
    }

    public function storeInvoicePdf(Order $order, Invoice $invoice): string
    {
        $path = 'invoices/'.$invoice->invoice_no.'.pdf';
        Storage::disk('public')->put($path, $this->invoicePdf($order)->output());

        if ($invoice->invoice_pdf !== $path) {
            $invoice->update(['invoice_pdf' => $path]);
        }

        return $path;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function makePdf(string $view, array $data): DomPdf
    {
        return Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
            ]);
    }

    private function logoSrc(): ?string
    {
        $absolute = $this->store->logoAbsolutePath();

        if (! $absolute || ! is_file($absolute)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            return null;
        }

        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolute));
    }
}
