<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','quota'
    ];

    public function applications() {
        return $this->hasMany(Application::class);
    }
    

    public function quotas()
    {
        return $this->hasMany(DepartmentQuota::class, 'department_id');
    }

    // Relasi untuk keahlian
    public function skills()
    {
        return $this->hasMany(DepartmentSkill::class)->orderBy('position');
    }

    // Relasi untuk jurusan yang relevan
    public function majors()
    {
        return $this->hasMany(DepartmentMajor::class)->orderBy('position');
    }
}

