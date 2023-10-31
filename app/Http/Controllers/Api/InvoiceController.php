<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->pelanggan_id;
        $pelanggan = Pelanggan::find($id);
        $invoices = Invoice::where('pelanggan_id', $id)
            ->where('total', '<>', 0)->latest()->get();
        $invoices->map(function ($invoice) {
            $invoice->updated_atFormat = $invoice->updated_at->translatedFormat('j F Y - H:i');
            $invoice->tagihan = $invoice->getTagihan();
            $invoice->tunggakan = $invoice->getTunggakan();
            $invoice->transaksis = Transaksi::with('user:id,name')->where('invoice_id', $invoice->id)->latest()->get();
            $invoice->transaksis->map(function ($t) {
                $t->created_atFormat = $t->created_at->translatedFormat('j F Y - H:i');
            });
        });
        $pelanggan->invoices = $invoices;
        return $pelanggan;
    }

    public function show($id)
    {
        $invoice = Invoice::with('pelanggan')->find($id);
        $invoice->tagihan = $invoice->getTagihan();
        $invoice->tunggakan = $invoice->getTunggakan();
        $invoice->transaksis = Transaksi::with('user:id,name')->where('invoice_id', $invoice->id)->latest()->get();
        $invoice->transaksis->map(function ($t) {
            $t->created_atFormat = $t->created_at->translatedFormat('j F Y - H:i');
        });
        return $invoice;
    }
}
