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
}
