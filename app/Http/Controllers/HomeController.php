<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getWarehouse(){
		$controller = new ProductController;
		return $controller->getWarehouse();
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $warehouse = $this->getWarehouse();

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $now        = date("Y-m-d H:i:s");
        $startDate  = date("Y-m-d H:i:s", strtotime($now. "-30 days"));

        $history = DB::table('stock')
                    ->leftJoin("products", "stock.product_id", "=", "products.product_id")
                    ->select("stock.*", "products.product_code", "products.product_name", DB::raw('SUM(stock.product_amount) as total'))
                    ->where([["stock.type", 0], ["stock.warehouse_id", $warehouse_id], ["products.product_name", "!=", null]])
                    ->whereBetween('stock.datetime', [$startDate, $now])
                    ->groupBy("stock.product_id")
                    ->get();

        return View::make("home")->with(compact("warehouse", "history"));
    }
}
