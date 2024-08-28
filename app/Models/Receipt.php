<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Tax;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'paid', 'total', 'taxTotal', 'clientId'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class)->withPivot('paymentMethodId', 'taxId', 'total', 'taxAmount');
    }
}
