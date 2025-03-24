<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreadOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bread_type_id',
        'delivery_date',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breadType()
    {
        return $this->belongsTo(BreadType::class);
    }
}