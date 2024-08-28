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
}
