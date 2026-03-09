<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentQuota extends Model
{
    protected $fillable = [
        'department_id',
        'quota',
        'period_start',
        'period_end',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
