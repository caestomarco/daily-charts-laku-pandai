<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model
{
    use HasFactory;

    protected $table = 'agents';

    public $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'name',
        'status',
        'account',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function dailyTransactions()
    {
        return $this->hasMany(DailyTransaction::class);
    }
}
