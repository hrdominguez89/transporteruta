<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\PaymentMethod;

class DriverSettlement extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'date', 'total', 'liquidated', 'dateFrom', 'dateTo', 'driverId', 'travelCertificateId', 'paymentMethodId'];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId');
    }

    public function travelCertificates()
    {
        return $this->hasMany(TravelCertificate::class, 'driverSettlementId');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'paymentMethodId');
    }
}
