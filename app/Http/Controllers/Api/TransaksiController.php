<?php


namespace App\Http\Controllers\Api;

use App\Models\Paket;

use App\Models\Invoice;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(
            'pelanggan:id,nama,region_id,server_id',
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
            $invoice->tagihan = $invoice->getTagihan();
            $t->invoice = $invoice;
            $t->created_atFormat = $t->created_at->translatedFormat('j F Y - H:i');
        });
        return $transaksi;
    }
    public function export(Request $request)
    {
        $query = Transaksi::with(
            'pelanggan:id,nama,alamat,created_at',
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

        $transaksi = $query->get();

        $data = [];
        foreach ($transaksi as $i => $tr) {
            $invoice = Invoice::find($tr->invoice_id);
            $paket = Paket::find($invoice->paket_id);
            $data[$i]['id'] = 'TR'.$tr->id;
            $data[$i]['user'] =  $tr->pelanggan->nama;
            $data[$i]['alamat'] =  $tr->pelanggan->alamat;
            $data[$i]['bandwidth'] =  $paket->bandwidth . ' Mbps';
            $data[$i]['menunggak'] =  $invoice->getTunggakan().' Bulan';
            $data[$i]['total_tagihan'] =  $invoice->getTagihan();
            $data[$i]['nominal'] =  $tr->nominal;
            $data[$i]['jumlah_terbayar'] =  $invoice->total;
            $data[$i]['tanggal_bayar'] =  $tr->created_at->translatedFormat('l, j F Y');
            $data[$i]['tanggal_pasang'] =  $tr->pelanggan->created_at->translatedFormat('l, j F Y');
            $data[$i]['penerima'] =  $tr->user->name;
        }
        return $data;
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric'
        ], [
            'nominal.required' => 'Nominal tidak boleh kosong',
            'nominal.numeric' => 'Nominal harus berupa angka'
        ]);

        $invoice = Invoice::with('pelanggan')->find($id);
        $total = $invoice->total + $request->nominal;
        $tagihan = $invoice->getTagihan();

        if ($total > $tagihan) {
            return response([
                "message" => "Nominal melebihi batas",
                "errors" => [
                    "nominal" => [
                        "Nominal melebihi pembayaran"
                    ]
                ]
            ], 422);
        }

        if ($total == $tagihan) {
            $invoice->status = 1;
            $date_pay = $invoice->pelanggan->created_at->format('j');

            if (Invoice::where(['pelanggan_id' => $id, 'status' => 0])->count() == 0) {
                Invoice::create([
                    'pelanggan_id' => $invoice->pelanggan_id,
                    'paket_id' => $invoice->paket_id,
                    'pay_at' => Carbon::parse($date_pay . now()->addMonth()->format('-m-Y'))
                ]);
            }
        }

        $invoice->total = $total;
        $invoice->save();

        Transaksi::create([
            'invoice_id' => $id,
            'pelanggan_id' => $invoice->pelanggan_id,
            'user_id' => auth()->user()->id,
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
        if ($invoice->total < $invoice->getTagihan()) {
            $invoice->status = 0;
            $invoice->save();
        }


        $transaksi->delete();
        return response(['message' => 'Data transaksi berhasil dihapus'], 200);
    }

    public function edit($id)
    {
        $transaksi = Transaksi::with('pelanggan', 'user')->find($id);
        $invoice = Invoice::with('paket')->find($transaksi->invoice_id);
        $invoice->tagihan = $invoice->getTagihan();
        $invoice->tunggakan = $invoice->getTunggakan();
        $transaksi->invoice = $invoice;
        return response($transaksi);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric'
        ], [
            'nominal.required' => 'Nominal tidak boleh kosong',
            'nominal.numeric' => 'Nominal harus berupa angka'
        ]);

        $transaksi = Transaksi::find($id);
        $invoice = Invoice::find($transaksi->invoice_id);

        $total = $invoice->total - $transaksi->nominal + $request->nominal;
        $tagihan = $invoice->getTagihan();

        if ($total > $tagihan) {
            return response([
                "message" => "Nominal melebihi batas",
                "errors" => [
                    "nominal" => [
                        "Nominal melebihi pembayaran"
                    ]
                ]
            ], 422);
        }
        $transaksi->nominal = $request->nominal;
        $transaksi->save();
        if ($total == $tagihan) {
            $invoice->status = 1;
        } else {
            $invoice->status = 0;
        }
        $invoice->total = $total;
        $invoice->save();

        return response(["message" => "Transaksi berhasil diubah!"]);
    }
}
