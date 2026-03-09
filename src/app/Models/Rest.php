<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'rest_in_at',
        'rest_out_at',
        'requested_in_at',
        'requested_out_at',
    ];

    protected $casts = [
        'punched_in_at' => 'datetime',
        'punched_out_at' => 'datetime',
    ];

    public function Attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
