<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RpaResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'raw_json',
        'extracted_text',
        'fields'
    ];

    protected $casts = [
        'raw_json' => 'array',
        'fields' => 'array'
    ];

    public function application() {
        return $this->belongsTo(Application::class);
    }
}
