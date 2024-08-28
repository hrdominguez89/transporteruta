<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DriverSettlement;
use App\Models\Receipt;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function driverSettlements()
    {
        return $this->hasMany(DrverSettlement::class, 'paymentMethodId');
    }

    public function receipts()
    {
        return $this->belongsToMany(Receipt::class, 'paymentMethodId');
    }
}
