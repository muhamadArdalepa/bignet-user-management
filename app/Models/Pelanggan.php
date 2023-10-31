<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $incrementing = false;
    public function server()
    {
        return $this->belongsTo(Server::class);
    }
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function getInvoice()
    {
        $invoice = Invoice::with('paket')
        ->where([
            'pelanggan_id' => $this->id,
            'status'=>0,
        ])->first();
        $invoice->tunggakan = $invoice->getTunggakan();
        $invoice->tagihan = $invoice->getTagihan();
        return $invoice;
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
}
