<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_anggota',
        'name',
        'bin_binti',
        'jenis_kelamin',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'status',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'tanggal_wafat',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function kas()
    {
        return $this->hasMany(Kas::class);
    }

    // Relasi: 1 User memiliki banyak progres iuran wajib
    public function wajibkasProgress()
    {
        return $this->hasMany(WajibkasProgress::class);
    }
}
