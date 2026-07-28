<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['title', 'description', 'schedule_date', 'location', 'created_by', 'target_groups'])]
class Schedule extends Model
{
    protected $casts = [
        'target_groups' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
