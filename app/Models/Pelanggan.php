<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    public $incrementing = false;
    public function server()
    {
        return $this->belongsTo(Server::class);
    }
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function getStatus()
    {
        switch ($this->status) {
            case '1':
                return ['Aktif', 'success'];

            case '2':
                return ['Isolir', 'danger'];

            case '3':
                return ['Tidak Aktif', 'dark'];

            default:
                return 'Aktif';
        }
    }

    public function getInvoice()
    {
        $invoice = Invoice::where('pelanggan_id', $this->id)->latest()->first();
        if (now()->gte($invoice->pay_at)) {
            return (($this->getTunggakan() * $this->bulanan) - $invoice->total);
        }
        return 0;
    }
    public function getInvoiceData()
    {
        $invoiceData = Invoice::where('pelanggan_id', $this->id)->latest()->first();
        $transaksis = Transaksi::with('user')->where('invoice_id',$invoiceData->id)->latest()->get();
        $transaksis->map(function ($transaksi){
           $transaksi->created_atFormat = $transaksi->created_at->translatedFormat('j F Y - H:i');
        });
        $invoiceData->transaksis = $transaksis;
        return $invoiceData;
    }

    public function getTunggakan()
    {
        return Invoice::where('pelanggan_id', $this->id)->latest()->first()->created_at->diffInMonths(now()) + 1;
    }
}
