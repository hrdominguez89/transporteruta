<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TravelCertificate;
use App\Models\DriverSettlement;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'dni', 'address', 'city', 'phone', 'percent', 'type'];

    public function travelCertificates()
    {
        return $this->hasMany(TravelCertificate::class, 'driverId');
    }

    public function driverSettlements()
    {
        return $this->hasMany(DriverSettlement::class, 'driverId');
    }
}
