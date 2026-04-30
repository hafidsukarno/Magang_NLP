<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id','name','nim','university','major','program_studi','email','phone','status','hrd_note'
    ];

    public function application() {
        return $this->belongsTo(Application::class);
    }
}
