<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class penjualans extends Model
{
    use HasFactory;

    protected $fillable = ['totalharga','PelangganID'];
    protected $primaryKey = 'PenjualanID';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($penjualan) {
            $penjualan->tanggalpenjualan = now()->toDateString();
        });
    }

    protected $guarded = [];
    public function pelanggan()
    {
        return $this->belongsTo(pelanggans::class, 'PelangganID', 'PelangganID');
    }

    public function produk()
    {
        return $this->belongsTo(produks::class);
    }
    public function detailpenjualan()
    {
        return $this->hasMany(detailpenjualans::class, 'PenjualanID', 'PenjualanID');
    }
}
