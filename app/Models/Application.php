<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'registration_code',
        'type',
        'leader_name',
        'leader_email',
        'leader_phone',
        'university',
        'major',
        'department_id',
        'duration',
        'period_start',
        'period_end',
        'file_path',
        'status',
        'hrd_note',
        'leader_status',
        'leader_note',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->hasMany(ApplicationMember::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function rpaResult()
    {
        return $this->hasOne(RpaResult::class);
    }
}
