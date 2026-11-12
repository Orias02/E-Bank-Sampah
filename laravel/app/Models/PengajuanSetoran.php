<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanSetoran extends Model
{
    public const ANTAR = 'antar';
    public const JEMPUT = 'jemput';

    public const MENUNGGU = 'menunggu';
    public const DITERIMA = 'diterima';
    public const SELESAI = 'selesai';
    public const DITOLAK = 'ditolak';
    public const DIBATALKAN = 'dibatalkan';

    protected $table = 'pengajuan_setoran';

    protected $fillable = ['nasabah_id', 'metode', 'tanggal_rencana', 'catatan', 'status', 'alasan'];

    protected function casts(): array
    {
        return [
            'tanggal_rencana' => 'date',
        ];
    }

    public function kode(): string
    {
        return 'PGJ-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    /** Transaksi setoran yang tercatat saat pengajuan ini ditimbang admin. */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'pengajuan_setoran_id');
    }

    /** Batasi ke pengajuan milik nasabah di bank sampah admin (super admin: semua). */
    public function scopeDiCakupan(Builder $query, $admin): Builder
    {
        return $admin->bank_sampah_id
            ? $query->whereIn('nasabah_id', Nasabah::where('bank_sampah_id', $admin->bank_sampah_id)->select('id'))
            : $query;
    }

    /** Total perkiraan berat (kg). Relasi jenisSampah harus sudah dimuat. */
    public function perkiraanBerat(): float
    {
        return (float) $this->jenisSampah->sum(fn ($j) => (float) $j->pivot->perkiraan_berat_kg);
    }

    /** Estimasi nilai (rupiah) dari perkiraan berat x harga sekarang. Relasi jenisSampah harus sudah dimuat. */
    public function perkiraanNilai(): int
    {
        return (int) round($this->jenisSampah->sum(fn ($j) => (float) $j->pivot->perkiraan_berat_kg * (float) $j->harga_per_kg));
    }

    public function jenisSampah(): BelongsToMany
    {
        return $this->belongsToMany(JenisSampah::class, 'pengajuan_setoran_jenis')->withPivot('perkiraan_berat_kg');
    }
}