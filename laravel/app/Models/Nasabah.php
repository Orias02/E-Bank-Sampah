<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nasabah extends Model
{
    protected $table = 'nasabah';

    protected $fillable = [
        'user_id',
        'bank_sampah_id',
        'nama_lengkap',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'whatsapp',
        'email',
        'ktp_path',
        'kota',
        'kecamatan',
        'kelurahan',
        'rt',
        'rw',
        'alamat_lengkap',
        'status',
        'foto_path',
        'metode_pencairan',
        'nomor_rekening',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankSampah(): BelongsTo
    {
        return $this->belongsTo(BankSampah::class);
    }

    /** Nomor ID nasabah yang ditampilkan, mis. NAS-000012. */
    public function kode(): string
    {
        return 'NAS-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    public function pengajuanSetoran(): HasMany
    {
        return $this->hasMany(PengajuanSetoran::class);
    }

    /**
     * Saldo aktif (rupiah): setoran yang sudah selesai dikurangi penarikan
     * (penarikan yang masih diproses ikut mengurangi supaya saldo tidak ditarik dua kali).
     */
    public function saldo(): int
    {
        $masuk = $this->transaksi()->where('tipe', Transaksi::SETORAN)->where('status', Transaksi::SELESAI)->sum('nilai');
        $keluar = $this->transaksi()->where('tipe', Transaksi::PENARIKAN)->whereIn('status', [Transaksi::PROSES, Transaksi::SELESAI])->sum('nilai');

        return (int) ($masuk - $keluar);
    }

    /** Total berat sampah yang sudah selesai disetor (kg). */
    public function totalBeratDisetor(): float
    {
        return (float) $this->transaksi()->where('tipe', Transaksi::SETORAN)->where('status', Transaksi::SELESAI)->sum('berat_kg');
    }
}