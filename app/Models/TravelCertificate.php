<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Driver;
use App\Models\Client;
use App\Models\TravelItem;
use App\Models\Invoice;

class TravelCertificate extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'total', 'iva', 'date', 'destiny', 'clientId', 'driverId', 'invoiceId'];

    protected $casts = [
        'total' => 'decimal:2',
        'iva'   => 'decimal:2',
        'date'  => 'date',
    ];

    public function client()   { return $this->belongsTo(Client::class,  'clientId'); }
    public function driver()   { return $this->belongsTo(Driver::class,  'driverId'); }
    public function invoice()  { return $this->belongsTo(Invoice::class, 'invoiceId'); }
    public function vehicle(){ return $this->belongsTo(Vehicle::class,'vehicleId');}

    // Nota: FK camelCase en este proyecto
    public function travelItems()
    {
        return $this->hasMany(TravelItem::class, 'travelCertificateId');
    }

    
    public function recalcTotals(): void
    {
        $this->total = $this->total_calculado;
        $this->iva   = $this->iva_calculado;
        $this->save();
    }
}

