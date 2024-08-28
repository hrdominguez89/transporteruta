<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Receipt;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function receipts()
    {
        return $this->belongsToMany(Receipt::class);
    }
}
