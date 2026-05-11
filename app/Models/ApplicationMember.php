<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id','name','nim','email','phone'
    ];

    public function application() {
        return $this->belongsTo(Application::class);
    }
}
