<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TravelCertificate;

class TravelItem extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'price', 'departureTime', 'arrivalTime', 'totalTime', 'distance', 'travelCertificateId'];

    public function travelCerficate()
    {
        return $this->belongsTo(TravelCerficate::class, 'travelCertificateId');
    }
}
