<?php

namespace App\Http\Controllers\Api;

use App\Models\Region;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $regions = Region::select();
        if ($request->filled('term')) {
            $regions->where('name', 'LIKE', '%' . $request->term . '%');
        }
        return response($regions->get());
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:32'
        ], [
            'name.required' => 'Nama wilayah harus diisi.',
            'name.max' => 'Nama wilayah tidak boleh lebih dari 32 karakter.',
        ]);
        try {
            if ($request->filled('id')) {
                Region::where('id', $request->id)->update(['name' =>  ucwords($request->name)]);
                return response(['message' => 'Wilayah baru berhasil ditambah!']);
            }
            Region::create(['name' =>  ucwords($request->name)]);
            return response(['message' => 'Wilayah baru berhasil ditambah!']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            Region::find($id)->delete();
            return response(['message' => 'Wilayah berhasil dihapus']);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }
}
