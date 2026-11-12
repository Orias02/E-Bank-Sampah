<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    /** Perubahan harga hanya ditampilkan sebagai naik/turun selama sekian hari sejak diubah. */
    public const HARI_TREN = 30;

    protected $table = 'jenis_sampah';

    protected $fillable = [
        'nama',
        'kategori',
        'deskripsi',
        'harga_per_kg',
        'harga_sebelumnya',
        'harga_diperbarui_pada',
        'ikon',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga_per_kg' => 'integer',
            'harga_sebelumnya' => 'integer',
            'harga_diperbarui_pada' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Setiap kali harga diubah (lewat Eloquent, mis. dari dashboard admin atau tinker), harga lama dan
     * tanggal perubahan dicatat otomatis supaya katalog bisa menampilkan naik/turun.
     */
    protected static function booted(): void
    {
        static::saving(function (self $jenis) {
            if ($jenis->exists && $jenis->isDirty('harga_per_kg') && ! $jenis->isDirty('harga_sebelumnya')) {
                $jenis->harga_sebelumnya = $jenis->getOriginal('harga_per_kg');
                $jenis->harga_diperbarui_pada = now()->toDateString();
            }
        });
    }

    /** Selisih harga terhadap harga sebelumnya: positif naik, negatif turun, 0 stabil (atau perubahan sudah lama). */
    public function perubahanHarga(): int
    {
        if ($this->harga_sebelumnya === null || $this->harga_diperbarui_pada === null) {
            return 0;
        }

        if ($this->harga_diperbarui_pada->lt(now()->subDays(self::HARI_TREN)->startOfDay())) {
            return 0;
        }

        return $this->harga_per_kg - $this->harga_sebelumnya;
    }
}