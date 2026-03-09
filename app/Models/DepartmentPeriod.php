<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentPeriod extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'weeks', 'description', 'position'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
