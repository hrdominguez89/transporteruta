<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\TravelCertificate;
use App\Models\Receipt;
use App\Models\Credit;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'pointOfSale', 'date', 'total', 'iva', 'totalWhitIva', 'invoiced', 'paid', 'balance', 'clientId', 'receiptId'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function travelCertificates()
    {
        return $this->hasMany(TravelCertificate::class, 'invoiceId');
    }

    public function receipts()
    {
        return $this->belongsToMany(Receipt::class)->withPivot('paymentMethodId', 'taxId', 'total', 'taxAmount');
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, 'invoiceId');
    }
}
