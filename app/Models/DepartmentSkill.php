<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentSkill extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'name', 'level', 'description', 'position'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
