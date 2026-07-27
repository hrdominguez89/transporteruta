<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    public const TYPE_PALLET = 'PALLET';
    public const TYPE_BULTO  = 'BULTO';

    protected $fillable = [
        'type',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_PALLET,
            self::TYPE_BULTO,
        ];
    }
}