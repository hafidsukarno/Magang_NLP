<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentProdiMap extends Model
{
    protected $fillable = ['department_id', 'prodi_keyword'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
