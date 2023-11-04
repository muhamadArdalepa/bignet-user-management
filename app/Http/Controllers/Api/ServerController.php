<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index(Request $request)
    {
        $servers = Server::with('region');
        if ($request->filled('term')) {
            $term =  '%' . $request->term . '%';
            $servers->where('name', 'LIKE', $term)
                ->orWhereHas('region', function ($region) use ($term) {
                    $region->where('name', 'LIKE', $term);
                });
        }
        return response($servers->get());
    }
    public function edit($id)
    {
        $server = Server::find($id);
        $regions = Region::all();
        $data = [
            'server' => $server,
            'regions' => $regions
        ];
        return response($data);
    }
    public function store(Request $request)
    {
        $except = '';
        if ($request->filled('id')) {
            $except = ',' . $request->id . ',id';
        }
        $request->validate([
            'name' => 'required|max:32',
            'kode' => 'required|max:1|unique:servers,kode' . $except,
            'region_id' => 'required|exists:regions,id',
        ], [
            'name.required' => 'Nama server harus diisi.',
            'name.max' => 'Nama server tidak boleh lebih dari 32 karakter.',
            'kode.required' => 'Kode server harus diisi.',
            'kode.max' => 'Kode server tidak boleh lebih dari 1 karakter.',
            'kode.unique' => 'Kode server telah digunakan',
            'region_id.required' => 'Nama server harus diisi.',
            'region_id.exist' => 'Wilayah ini tidak ada di database',
        ]);
        $data = [
            'name' =>  ucwords($request->name),
            'kode' =>  strtoupper($request->kode),
            'region_id' =>  $request->region_id,
        ];
        try {
            if ($request->filled('id')) {
                Server::where('id', $request->id)->update($data);
                return response(['message' => 'Server baru berhasil ditambah!']);
            }
            Server::create($data);
            return response(['message' => 'Server baru berhasil ditambah!']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            Server::find($id)->delete();
            return response(['message' => 'Server berhasil dihapus']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
}
