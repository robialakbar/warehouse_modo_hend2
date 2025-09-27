<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderSyncController extends Controller
{
    public function index()
    {
        // Ambil semua order yang memiliki ketidaksesuaian total
        $orders = Order::with('details')->where('total_harga', '!=', 0)->get();

        return view('orders.sync', compact('orders'));
    }

    public function sync($order_id)
    {
        $order = Order::with('details')->findOrFail($order_id);

        $oldTotal = $order->total_harga;
        $order->total_harga = $order->calculateTotalFromDetails();
        $order->save();

        // log
        Log::info('Order disinkronisasi', [
            'order_id' => $order->order_id,
            'old_total' => $oldTotal,
            'new_total' => $order->calculateTotalFromDetails(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return redirect()->back()->with('success', 'Order berhasil disinkronisasi.');
    }

    public function syncAll()
    {
        $orders = Order::with('details')->where('total_harga', '!=', 0)->notSynced()->get();
        $count = 0;

        foreach ($orders as $order) {
            $calculatedTotal = $order->calculateTotalFromDetails();

            if ($calculatedTotal != $order->total_harga) {
                $oldTotal = $order->total_harga;

                $order->total_harga = $calculatedTotal;
                $order->save();
                $count++;

                // log
                Log::info('Order disinkronisasi', [
                    'order_id' => $order->order_id,
                    'old_total' => $oldTotal,
                    'new_total' => $calculatedTotal,
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
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
