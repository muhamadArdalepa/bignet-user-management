<?php

namespace App\Http\Controllers\Api;

use App\Models\Server;
use App\Models\Invoice;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::with(
            'server:id,name',
            'region:id,name',
        );

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
        $request->validate([
            'nama' => 'required|min:3|max:32',
            'no_telp' => 'required|min:11|max:15',
            'email' => 'required|email|unique:pelanggans,email',
            'server_id' => 'required|exists:servers,id',
            'mac' => 'required',
            'alamat' => 'required',
            'created_at' => 'required|date|before_or_equal:now',
            'paket_id' => 'required|exists:pakets,id',
        ], [
            'nama.required' => 'Kolom Nama wajib diisi.',
            'nama.min' => 'Kolom Nama harus memiliki setidaknya 3 karakter.',
            'nama.max' => 'Kolom Nama maksimal memiliki 32 karakter.',
            'no_telp.required' => 'Kolom Nomor Telepon wajib diisi.',
            'no_telp.min' => 'Kolom Nomor Telepon harus memiliki setidaknya 11 karakter.',
            'no_telp.max' => 'Kolom Nomor Telepon maksimal memiliki 15 karakter.',
            'email.required' => 'Kolom Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'server_id.required' => 'Kolom ID Server wajib diisi.',
            'server_id.exists' => 'ID Server yang dipilih tidak valid.',
            'mac.required' => 'Kolom MAC Address wajib diisi.',
            'alamat.required' => 'Kolom Alamat wajib diisi.',
            'created_at.required' => 'Kolom Tanggal Pembuatan wajib diisi.',
            'created_at.date' => 'Kolom Tanggal Pembuatan harus berisi tanggal yang valid.',
            'created_at.before_or_equal' => 'Kolom Tanggal Pembuatan harus sebelum atau sama dengan tanggal saat ini.',
            'paket_id.required' => 'Kolom ID Paket wajib diisi.',
            'paket_id.exists' => 'ID Paket yang dipilih tidak valid.'
        ]);

        try {
            $server = Server::find($request->server_id);
            $int_id = str_pad(intval(substr(Pelanggan::where('server_id', $request->server_id)->orderBy('id', 'desc')->first()->id, 1)) + 1, 4, "0", STR_PAD_LEFT);
            $id = $server->kode . $int_id;
            $va = str_pad($server->id, 2, "0", STR_PAD_LEFT) .  $int_id;

            DB::beginTransaction();
            try {
                Pelanggan::create([
                    'id' => $id,
                    'nama' => ucwords($request->nama),
                    'va' => $va,
                    'no_telp' => $request->no_telp,
                    'email' => $request->email,
                    'server_id' => $request->server_id,
                    'region_id' => $server->region_id,
                    'mac' => $request->mac,
                    'alamat' => $request->alamat,
                    'created_at' => Carbon::parse($request->created_at)
                ]);

                Invoice::create([
                    'pelanggan_id' => $id,
                    'paket_id' => $request->paket_id,
                    'pay_at' => Carbon::parse($request->created_at)->addMonth()->format('Y-m-d')
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            return response(['message' => 'Pelanggan berhasil ditambah']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pelanggan = Pelanggan::select(
            'nama',
            'no_telp',
            'email',
            'server_id',
            'mac',
            'alamat',
            'created_at',
        )->where('id', $id)->first();

        if (!$pelanggan) {
            return response(['message' => 'Pelanggan tidak ada'], 404);
        }
        $pelanggan->paket_id = Invoice::where(['pelanggan_id' => $id, 'status' => 0])->first()->paket_id;
        $pelanggan->created_atFormat = $pelanggan->created_at->format('Y-m-d');
        return response($pelanggan);
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
        $request->validate([
            'nama' => 'required|min:3|max:32',
            'no_telp' => 'required|min:11|max:15',
            'email' => 'required|email|unique:pelanggans,email,' . $id . ',id',
            'server_id' => 'required|exists:servers,id',
            'mac' => 'required',
            'alamat' => 'required',
            'created_at' => 'required|date|before_or_equal:now',
            'paket_id' => 'required|exists:pakets,id',
        ], [
            'nama.required' => 'Kolom Nama wajib diisi.',
            'nama.min' => 'Kolom Nama harus memiliki setidaknya 3 karakter.',
            'nama.max' => 'Kolom Nama maksimal memiliki 32 karakter.',
            'no_telp.required' => 'Kolom Nomor Telepon wajib diisi.',
            'no_telp.min' => 'Kolom Nomor Telepon harus memiliki setidaknya 11 karakter.',
            'no_telp.max' => 'Kolom Nomor Telepon maksimal memiliki 15 karakter.',
            'email.required' => 'Kolom Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Alamat email sudah digunakan.',
            'server_id.required' => 'Kolom ID Server wajib diisi.',
            'server_id.exists' => 'ID Server yang dipilih tidak valid.',
            'mac.required' => 'Kolom MAC Address wajib diisi.',
            'alamat.required' => 'Kolom Alamat wajib diisi.',
            'created_at.required' => 'Kolom Tanggal Pembuatan wajib diisi.',
            'created_at.date' => 'Kolom Tanggal Pembuatan harus berisi tanggal yang valid.',
            'created_at.before_or_equal' => 'Kolom Tanggal Pembuatan harus sebelum atau sama dengan tanggal saat ini.',
            'paket_id.required' => 'Kolom ID Paket wajib diisi.',
            'paket_id.exists' => 'ID Paket yang dipilih tidak valid.'
        ]);

        try {
            $server = Server::find($request->server_id);

            DB::beginTransaction();
            try {
                Pelanggan::where('id', $id)->update([
                    'nama' => ucwords($request->nama),
                    'no_telp' => $request->no_telp,
                    'email' => $request->email,
                    'server_id' => $request->server_id,
                    'region_id' => $server->region_id,
                    'mac' => $request->mac,
                    'alamat' => $request->alamat,
                    'created_at' => Carbon::parse($request->created_at)
                ]);

                Invoice::where(['status' => 0, 'pelanggan_id' => $id])->update([
                    'paket_id' => $request->paket_id,
                    'pay_at' => date('Y') . Carbon::parse($request->created_at)->addMonth()->format('-m-d')
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            return response(['message' => 'Pelanggan berhasil ditambah']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Pelanggan::find($id)->delete();
            return response(['message' => 'Pelanggan berhasil dihapus']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
}
