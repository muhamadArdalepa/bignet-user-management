<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }
    public function getTotal()
    {
        $pelanggan = Pelanggan::find($this->pelanggan_id);
        if ($this->status == 1) {
            return $this->total;
        }
        if (now()->gte($this->pay_at)) {
            return ($pelanggan->getTunggakan() * $pelanggan->bulanan);
        }
        return $this->total;
    }

}
