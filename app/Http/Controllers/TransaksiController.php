<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(
            'pelanggan:id,nama,region_id,server_id,bulanan',
            'user:id,name',
        )->latest();

        if ($request->filled('r')) {
            $r = $request->r;
            $query->whereHas('pelanggan', function ($t) use ($r) {
                $t->where('region_id', $r);
            });
        }
        if ($request->filled('s')) {
            $s = '%' . $request->s . '%';
            $query->where(function ($query) use ($s) {
                $query->orWhere('id', 'LIKE', $s)
                    ->orWhere('nominal', 'LIKE', $s)
                    ->orWhereHas('pelanggan', function ($query) use ($s) {
                        $query->where('nama', 'LIKE', $s);
                    })
                    ->orWhereHas('user', function ($query) use ($s) {
                        $query->where('name', 'LIKE', $s);
                    });
            });
        }
        if ($request->filled('g')) {
            $g = $request->g;
            $query->whereDate('created_at', $g);
        }

        if ($request->filled('v')) {
            $v = $request->v;
            $query->whereHas('pelanggan', function ($query) use ($v) {
                $query->where('server_id', $v);
            });
        }

        $transaksi = $query->get();
        $transaksi->map(function ($t) {
            $invoice = Invoice::find($t->invoice_id);
            $invoice->get_total = $invoice->getTotal();
            $t->invoice = $invoice;
            $t->created_atFormat = $t->created_at->translatedFormat('j F Y - H:i');
            return $t;
        });
        return $transaksi;
    }
    public function store(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric'
        ], [
            'nominal.required' => 'Nominal tidak boleh kosong',
            'nominal.numeric' => 'Nominal harus berupa angka'
        ]);

        $invoiceData = Invoice::where('pelanggan_id', $id)->latest()->first();
        $invoiceData->total = $invoiceData->total + $request->nominal;

        $pelanggan = Pelanggan::find($id);

        if ($request->nominal > $pelanggan->getInvoice()) {
            return response([
                "message" => "Nominal melebihi batas",
                "errors" => [
                    "nominal" => [
                        "Nominal melebihi pembayaran"
                    ]
                ]
            ], 422);
        }

        if ($request->nominal == $pelanggan->getInvoice()) {
            $invoiceData->status = 1;
            $pelanggan->status = 1;
            $pelanggan->save();

            $date_pay = $pelanggan->created_at->format('j');

            Invoice::create([
                'pelanggan_id' => $id,
                'pay_at' => Carbon::parse($date_pay . now()->addMonth()->format('-m-Y'))
            ]);
        }

        $invoiceData->save();

        Transaksi::create([
            'invoice_id' => $invoiceData->id,
            'pelanggan_id' => $id,
            'user_id' => 1,
            'nominal' => $request->nominal
        ]);
        return response(["message" => "Pembayaran berhasil"]);
    }
    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);
        $invoice = Invoice::find($transaksi->invoice_id);

        $invoice->total = $invoice->total - $transaksi->nominal;
        $invoice->save();

        $transaksi->delete();
        return response(['message' => 'Data transaksi berhasil dihapus'], 200);
    }
    public function edit($id)
    {
        $transaksi = Transaksi::with('pelanggan','invoice','user')->find($id);
        return response($transaksi);
    }
}
