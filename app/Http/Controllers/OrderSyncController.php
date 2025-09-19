<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderSyncController extends Controller
{
    public function index()
    {
        // Ambil semua order yang memiliki ketidaksesuaian total
        $orders = Order::with('details')->where('total_harga', '!=', 0)->notSynced()->get();

        return view('orders.sync', compact('orders'));
    }

    public function sync($order_id)
    {
        $order = Order::with('details')->findOrFail($order_id);

        // Update total_harga2 di orders
        $order->total_harga = $order->calculateTotalFromDetails();
        $order->save();

        return redirect()->back()->with('success', 'Order berhasil disinkronisasi.');
    }

    public function syncAll()
    {
        $orders = Order::with('details')->where('total_harga', '!=', 0)->notSynced()->get();
        $count = 0;

        foreach ($orders as $order) {
            $calculatedTotal = $order->calculateTotalFromDetails();
            // dd($calculatedTotal, $order->total_harga);
            if ($calculatedTotal != $order->total_harga) {
                $order->total_harga = $calculatedTotal;
                $order->save();
                $count++;
            }
        }

        return redirect()
            ->back()
            ->with('success', "{$count} order berhasil disinkronisasi.");
    }

    public function show($order_id)
    {
        $order = Order::with('details')->findOrFail($order_id);
        return view('orders.show', compact('order'));
    }
}
