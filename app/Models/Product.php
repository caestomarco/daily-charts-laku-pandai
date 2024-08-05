<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    public $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
        ];
    }

    public function dailyTransactions()
    {
        return $this->hasMany(DailyTransaction::class);
    }
}
