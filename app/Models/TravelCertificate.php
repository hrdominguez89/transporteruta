<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Driver;
use App\Models\Client;
use App\Models\TravelItem;

class TravelCertificate extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'total', 'iva', 'date', 'destiny', 'clientId', 'driverId', 'invoiceId'];

    protected $casts = [
        'total' => 'decimal:2',
        'iva'   => 'decimal:2',
        'date'  => 'date',
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId');
    }

    public function travelItems()
    {
        return $this->hasMany(TravelItem::class, 'travelCertificateId');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceId');
    }
    public function recalcTotals(): void
    {
        $total   = (float) $this->travelItems()->sum('price');
        $ivaBase = (float) $this->travelItems()->where('type', '!=', 'PEAJE')->sum('price');

        $this->total = round($total, 2);
        $this->iva   = round($ivaBase * 0.21, 2);
        $this->save();
    }
}
