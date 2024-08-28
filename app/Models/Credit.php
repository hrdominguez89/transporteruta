<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Invoice;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'total', 'clientId', 'invoiceId'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceId');
    }
}
