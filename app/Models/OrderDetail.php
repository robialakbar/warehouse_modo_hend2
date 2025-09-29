<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'orders_detail';
    protected $primaryKey = 'order_detail_id';
    public $timestamps = false;

    protected $fillable = ['order_id', 'type', 'product_id', 'price', 'amount'];

    /**
     * Relationship dengan Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /**
     * Hitung subtotal untuk detail ini
     */
    public function getSubtotalAttribute()
    {
        return $this->price * $this->amount;
    }

    /**
     * Accessor untuk subtotal
     */
    public function subtotal()
    {
        return $this->price * $this->amount;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
