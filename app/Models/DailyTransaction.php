<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyTransaction extends Model
{
    use HasFactory;

    protected $table = 'daily_transactions';

    public $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'agent_id',
        'product_id',
        'source_account',
        'nominal',
        'admin_fee',
        'total',
        'status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'created_at' => 'date',
            'updated_at' => 'date',
        ];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
