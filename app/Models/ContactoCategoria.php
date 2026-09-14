<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoCategoria extends Model
{
    protected $fillable = ['contacto_id', 'categoria'];

    public function contacto()
    {
        return $this->belongsTo(Contacto::class);
    }
}
