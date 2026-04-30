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
        'leader_nim',
        'leader_email',
        'leader_phone',
        'university',
        'major',
        'keahlian',
        'program_studi',
        'department_id',
        'duration',
        'period_start',
        'period_end',
        'file_path',
        'status',
        'hrd_note',
        'leader_status',
        'leader_note',
        'surat_permohonan_path',
        'surat_permohonan_extracted_text',
        'surat_permohonan_nama',
        'surat_permohonan_major',
        'surat_permohonan_type',
        'surat_laporan_path',
        'surat_laporan_extracted_text',
        'surat_laporan_title',
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
}
