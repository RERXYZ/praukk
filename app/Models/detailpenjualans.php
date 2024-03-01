<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailpenjualans extends Model
{
    use HasFactory;

    protected $fillable = ['PenjualanID','ProdukID','JumlahProduk','Subtotal'];
    protected $primaryKey = 'DetailID';

    protected $guarded = [];
    public function penjualan(){
        return $this->hasMany(penjualans::class, 'PenjualanID', 'PenjualanID');
    }
    public function produk(){
        return $this->belongsTo(produks::class, 'ProdukID', 'ProdukID');
    }
}
