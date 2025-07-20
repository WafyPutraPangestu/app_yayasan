<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kas extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'kas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tipe',
        'user_id',
        'jenis_kas_id',
        'jumlah',
        'keterangan',
        'tanggal',
        'bulan_iuran',
        'tahun_iuran',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal' => 'date',
    ];

    /**
     * Mendefinisikan relasi bahwa satu transaksi kas ini dimiliki oleh satu User (Anggota).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mendefinisikan relasi bahwa satu transaksi kas ini memiliki satu Jenis Kas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jenisKas(): BelongsTo
    {
        return $this->belongsTo(JenisKas::class, 'jenis_kas_id');
    }
}
