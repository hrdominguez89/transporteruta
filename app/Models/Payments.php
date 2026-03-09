<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }
    public function obtenerRecibos()
    {
        
        return $this->belongsToMany(Receipt::class, 'payment_recipe_pivot', 'paymentId', 'recipeId')
            ->withPivot('total');
    }
    public function agregadoAlRecibo($id){
         return $this->obtenerRecibos()->wherePivot('recipeId', $id)->exists();
    }
}
