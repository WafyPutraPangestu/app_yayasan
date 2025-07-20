<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKas extends Model
{
    use HasFactory;

    protected $table = 'jenis_kas';

    protected $fillable = [
        'nama_jenis_kas',
        'target_lunas',
        'tipe_iuran',
        'nominal_wajib',
        'default_tipe',
        'status',
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
