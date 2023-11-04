<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function index(Request $request)
    {
        $pakets = Paket::select();
        if ($request->filled('term')) {
            $pakets->where('name', 'LIKE', '%' . $request->term . '%')
                ->orWhere('bandwidth', 'LIKE', '%' . $request->term . '%')
                ->orWhere('harga', 'LIKE', '%' . $request->term . '%');
        }
        return response($pakets->get());
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|max:32',
            'harga' => 'required|numeric',
            'bandwidth' => 'required|numeric',
        ], [
            'name.max' => 'Nama paket tidak boleh lebih dari 32 karakter.',
            'harga.required' => 'Harga paket harus diisi',
            'harga.numeric' => 'Harga paket harus berupa angka',
            'bandwidth.required' => 'Bandwidth harus diisi',
            'bandwidth.numeric' => 'Bandwidth harus berupa angka',
        ]);
        $data = [
            'name' =>  ucwords($request->name),
            'harga' =>  $request->harga,
            'bandwidth' =>  $request->bandwidth
        ];
        try {
            if ($request->filled('id')) {
                Paket::where('id', $request->id)->update($data);
                return response(['message' => 'Paket baru berhasil ditambah!']);
            }
            Paket::create($data);
            return response(['message' => 'Paket baru berhasil ditambah!']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            Paket::find($id)->delete();
            return response(['message' => 'Server berhasil dihapus']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
}
