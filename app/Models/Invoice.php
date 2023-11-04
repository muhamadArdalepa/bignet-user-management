<?php

namespace App\Models;

use App\Models\Paket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }
    public function getTunggakan()
    {
        if ($this->status == 0) {
            return \Carbon\Carbon::parse($this->pay_at)->diffInMonths(\Carbon\Carbon::parse(date('d-m-Y'))) + 1;
        }
        return \Carbon\Carbon::parse($this->pay_at)->diffInMonths(\Carbon\Carbon::parse($this->updated_at->format('d-m-Y'))) + 1;
    }
    public function getTagihan()
    {
        if ($this->status == 1) {
            return $this->total;
        }
        if (now()->gte($this->pay_at)) {
            return ($this->getTunggakan() * $this->paket->harga);
        }
        return $this->total;
    }
}
