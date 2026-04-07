<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Driver;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function drivers()
    {
        return $this->hasMany(Driver::class, 'vehicleId');
    }
    public function recaudacionHistorica()
    {
        return TravelCertificate::where('vehicleId',$this->id)->sum('total');
    }
    public function peajesHistoricos()
    {
        $viajes = TravelCertificate::where('vehicleId',$this->id)
        ->get();
        return $viajes->sum(fn($viaje) => $viaje->totalpeajes);

    }
    public function reacaudacionPeriodo($desde,$hasta)
    {
        return TravelCertificate::where('vehicleId',$this->id)
        ->whereBetween('date',[
            $desde,
            $hasta
        ])
        ->sum('total');
    }
    public function peajesPeriodo($desde,$hasta)
    {
        $viajes = TravelCertificate::where('vehicleId',$this->id)
        ->whereBetween('date',[
            $desde,
            $hasta
            ])
        ->get();
        
        return $viajes->sum(fn($viaje) => $viaje->totalpeajes);
    }
}
