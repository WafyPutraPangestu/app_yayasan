<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKas extends Model
{
    use HasFactory;

    protected $table = 'jenis_kas';

    protected $fillable = [
        'kode_jenis_kas',
        'nama_jenis_kas',
        'target_lunas',
        'tipe_iuran',
        'nominal_wajib',
        'default_tipe',
        'status',
    ];

    protected $casts = [
        'target_lunas' => 'integer',
        'nominal_wajib' => 'integer',
    ];
    public function kas()
    {
        return $this->hasMany(Kas::class);
    }

    public function wajibkasProgress()
    {
        return $this->hasMany(WajibkasProgress::class);
    }
}
