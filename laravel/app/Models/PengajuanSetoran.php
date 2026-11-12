<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function jenisSampah(): BelongsToMany
    {
        return $this->belongsToMany(JenisSampah::class, 'pengajuan_setoran_jenis')->withPivot('perkiraan_berat_kg');
    }
}