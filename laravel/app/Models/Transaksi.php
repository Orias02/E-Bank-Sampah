<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    public const SETORAN = 'setoran';
    public const PENARIKAN = 'penarikan';

    public const PROSES = 'proses';
    public const SELESAI = 'selesai';
    public const DIBATALKAN = 'dibatalkan';

    protected $table = 'transaksi';

    protected $fillable = [
        'nasabah_id',
        'jenis_sampah_id',
        'tipe',
        'metode',
        'nomor_tujuan',
        'nama_pemilik',
        'berat_kg',
        'nilai',
        'status',
        'keterangan',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'berat_kg' => 'float',
            'nilai' => 'integer',
        ];
    }

    public function kode(): string
    {
        return 'TRX-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class);
    }
}