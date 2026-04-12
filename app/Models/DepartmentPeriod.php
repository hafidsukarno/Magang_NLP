<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentPeriod extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'duration', 'description', 'position', 'period_start', 'period_end'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
