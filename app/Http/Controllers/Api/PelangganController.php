<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::with('server:id,name', 'region:id,name');

        if ($request->filled('r')) {
            $query->where('region_id', $request->r);
        }

        if ($request->filled('v')) {
            $v = explode(',', $request->v);
            $query->whereIn('server_id', $v);
        }

        if ($request->filled('i')) {
            $i = explode(',', $request->i);
            $query->whereIn('status', $i);
        }


        if ($request->filled('g')) {
            $query->whereDay('created_at', $request->g);
        }


        if ($request->filled('s')) {
            $s = '%' . $request->s . '%';
            $query->where(function ($query) use ($s) {
                $query->orWhere('id', 'LIKE', $s)
                    ->orWhere('nama', 'LIKE', $s)
                    ->orWhere('no_telp', 'LIKE', $s)
                    ->orWhere('email', 'LIKE', $s)
                    ->orWhere('va', 'LIKE', $s)
                    ->orWhere('mac', 'LIKE', $s);
            });
        }

        $o1 = $request->filled('o1') ? $request->o1 : 'id';
        $o2 = $request->filled('o2') ? $request->o2 : 'asc';
        $query->orderBy($o1, $o2);

        $pelanggans = $query->get();

        $pelanggans->map(function ($pelanggan) {
            $pelanggan->tanggal = $pelanggan->created_at->translatedFormat('j F Y');
            $pelanggan->status = $pelanggan->getStatus();
            $pelanggan->invoice = $pelanggan->getInvoice();
            $pelanggan->tunggakan = $pelanggan->getTunggakan();
            return $pelanggan;
        });
        return response($pelanggans);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pelanggan = Pelanggan::find($id);
        $pelanggan->tanggal = $pelanggan->created_at->translatedFormat('j F Y');
        $pelanggan->invoice = $pelanggan->getInvoice();
        $pelanggan->tunggakan = $pelanggan->getTunggakan();
        $pelanggan->invoiceData = $pelanggan->getInvoiceData();
        return response($pelanggan);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function isolir(Pelanggan $pelanggan)
    {
        $pelanggan->status = $pelanggan->status == 1 ? 2 : 1;
        $pelanggan->save();
        return response(['message' => ($pelanggan->status == 1 ? 'Aktivasi' : 'Isolir') . ' user berhasil!']);
    }
    public function isolirBatch(Request $request)
    {
        Pelanggan::whereIn('id', $request->pelanggans)->update(['status' => $request->i]);
        return response(['message' => 'Isolir ' . count($request->pelanggans) . ' user berhasil!']);
    }
    public function invoice($id)
    {
        $pelanggan = Pelanggan::find($id);
        $invoice = Invoice::where('pelanggan_id', $id)->where('total', '<>', 0)->get();
        $invoice->map(function ($i) {
            $transaksi = Transaksi::with('user')->where('invoice_id', $i->id)->latest()->get();
            $transaksi->map(function ($t) {
                $t->created_atFormat = $t->created_at->translatedFormat('j F Y - H:i');
                return $t;
            });
            $i->updated_atFormat = $i->updated_at->translatedFormat('j F Y - H:i');
            $i->transaksis = $transaksi;
            return $i;
        });
        $pelanggan->invoice = $invoice;
        return $pelanggan;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
