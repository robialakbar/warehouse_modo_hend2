<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $timestamps = false;

    protected $fillable = ['warehouse_id', 'order_type', 'tgl_order', 'tgl_invoice', 'no_invoice', 'user_id', 'customer_name', 'customer_nohp', 'customer_alamat', 'kendaraan_nopol', 'kendaraan_jenis', 'kendaraan_km', 'kendaraan_tahun', 'total_harga', 'total_harga2', 'diskon', 'metode_pembayaran', 'pembayaran', 'kembalian', 'nama_mekanik', 'catatan', 'status'];

    protected $casts = [
        'tgl_order' => 'datetime',
        'tgl_invoice' => 'datetime',
    ];

    /**
     * Relationship dengan OrderDetail
     */
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    /**
     * Hitung total dari detail order
     */
    public function calculateTotalFromDetails()
    {
        return $this->details->sum(function ($detail) {
            return $detail->price * $detail->amount;
        });
    }

    /**
     * Cek apakah total order sesuai dengan detail
     */
    public function isTotalMatch()
    {
        return $this->total_harga == $this->calculateTotalFromDetails();
    }

    /**
     * Scope untuk mencari order yang tidak sync
     */
    public function scopeNotSynced($query)
    {
        return $query->whereHas('details', function ($q) {
            $q->selectRaw('order_id, SUM(price * amount) as total')->groupBy('order_id')->havingRaw('total != orders.total_harga');
        });
    }
}
