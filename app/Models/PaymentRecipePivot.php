<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRecipePivot extends Model
{
    protected $table = 'payment_recipe_pivot';

    protected $fillable = ['paymentId', 'recipeId', 'total'];

    public function payment()
    {
        return $this->belongsTo(Payments::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
}