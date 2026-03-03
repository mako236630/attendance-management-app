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

    public function Attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
