<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WajibkasProgress extends Model
{
    use HasFactory;

    protected $table = 'wajibkas_progress';

    protected $fillable = [
        'user_id',
        'jenis_kas_id',
        'total_terbayar',
        'status',
        'tanggal_lunas',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke JenisKas
    public function jenisKas()
    {
        return $this->belongsTo(JenisKas::class);
    }
}
