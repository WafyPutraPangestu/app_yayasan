<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // id user PK
            $table->string('id_anggota', 10)->unique(); // contoh 25-001
            $table->string('name', 100);
            $table->string('bin_binti', 100)->nullable(); // Ditambahkan: Bin/Binti
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable(); // Ditambahkan: Jenis Kelamin
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('google_id')->nullable();
            $table->string('avatar')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['user', 'admin'])->default('user'); // user/admin
            $table->enum('status', ['Pending', 'Aktif', 'Nonaktif', 'Wafat', 'Mengundurkan diri'])->default('Pending'); // Ditambah Nonaktif dan default Pending
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable(); // Ditambahkan: Alamat saat ini
            $table->string('no_hp', 20)->nullable();
            $table->date('tanggal_wafat')->nullable(); // Ditambahkan: Tanggal Wafat
            $table->rememberToken();
            $table->timestamps();

            // Index untuk kolom yang sering digunakan
            $table->index(['id_anggota']);
            $table->index(['role']);
            $table->index(['status']);
            $table->index(['tanggal_lahir']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
