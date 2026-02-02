<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TravelCertificate;
use App\Models\Invoice;
use App\Models\Credit;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'dni', 'address', 'city', 'phone', 'ivaType', 'observations'];

    public function travelCertificates()
    {
        return $this->hasMany(TravelCertificate::class, 'clientId');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'clientId');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'clientId');
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, 'clientId');
    }
    public function debits()
    {
        return $this->hasMany(Debit::class, 'clientId');
    }
}
