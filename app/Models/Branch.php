<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    public $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
        ];
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
}
