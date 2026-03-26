<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Quotation;
use App\Services\QuotationService;

class QuotationController extends Controller
{
    public function create()
    {
        $settings = GeneralSetting::first();
        if ($settings && ! $settings->isQuotationsEnabled()) {
            abort(404);
        }

        return view('quotations.create');
    }

    public function confirmation(Quotation $quotation)
    {
        if ($quotation->user_id !== auth()->id()) {
            abort(403);
        }

        $quotation->load('items.product');

        return view('quotations.confirmation', compact('quotation'));
    }

    public function myQuotations()
    {
        $quotations = Quotation::forUser(auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('quotations.index', compact('quotations'));
    }

    public function show(Quotation $quotation)
    {
        if ($quotation->user_id !== auth()->id()) {
            abort(403);
        }

        $quotation->load(['items.product', 'approvedByUser', 'rejectedByUser', 'convertedOrder']);

        return view('quotations.show', compact('quotation'));
    }

    public function downloadPdf(Quotation $quotation)
    {
        if ($quotation->user_id !== auth()->id()) {
            abort(403);
        }

        $service = app(QuotationService::class);
        $pdf = $service->generatePdf($quotation);

        return $pdf->download('cotizacion-' . $quotation->quotation_number . '.pdf');
    }
}
