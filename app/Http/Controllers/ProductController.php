<?php

namespace App\Http\Controllers;

use App\Exports\HistoryExport;
use App\Exports\WIPHistoryExport;
use App\Exports\ProductsExport;
use App\Exports\LaporanExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use DNS1D;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PDF;
use Excel;
use App\Imports\ProductsImport;
use Spatie\PdfToImage\Pdf as PDF2Image;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        if(Auth::check() && !Session::has('selected_warehouse_id')){
            if(Auth::user()->role == 0){
                $warehouse = DB::table('warehouse')->orderBy("warehouse_id", "asc")->first();
            } else {
                $warehouse = DB::table('warehouse')->where("city_id", Auth::user()->city_id)->orderBy("warehouse_id", "asc")->first();
            }
            Session::put('selected_warehouse_id', $warehouse->warehouse_id);
            Session::put('selected_warehouse_name', $warehouse->warehouse_name);
        }

    }

    public function products(Request $req){
        $sort           = $req->sort;
        $search         = $req->q;
        $cat            = $req->category;
        $dl             = $req->format;
        $hargaBeli      = $req->harga_beli;
        $hargaJual      = $req->harga_jual;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $products = DB::table('products')
                    ->leftJoin("categories", "products.category_id", "=", "categories.category_id")
                    ->select("products.*", "categories.*");

        $productsExport = $products;

        if(!empty($cat)){
            $products = $products->orWhere([["categories.category_id", $cat], ["products.warehouse_id", $warehouse_id]]);
            $productsExport = $productsExport->orWhere([["categories.category_id", $cat], ["products.warehouse_id", $warehouse_id]]);
        }

        if(!empty($search)){
            $products = $products
                        ->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]])
                        ->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]]);
        }

        if(!empty($sort)){
            if($sort == "category_az"){
                $products = $products->orderBy("categories.category_name", "asc");
            } else if($sort == "category_za"){
                $products = $products->orderBy("categories.category_name", "desc");
            } else if($sort == "name_az"){
                $products = $products->orderBy("products.product_name", "asc");
            } else if($sort == "name_za"){
                $products = $products->orderBy("products.product_name", "desc");
            } else {
                $products = $products->orderBy("products.product_id", "desc");
            }
        }

        $productsExport     = $productsExport->get();
        $warehouse          = $this->getWarehouse();
        $products           = $products->where("products.warehouse_id", $warehouse_id);
        $total_modal        = 0;
        $total_all_stock    = 0;

        if($req->format == "json"){
            $product_id = $req->product_id;

            if(!empty($product_id)){
                $products = $products->where("product_id", $product_id)->first();
                $products->sale_price = number_format($products->sale_price, 0, ",", ".");
            } else {
                $products = $products->get();
            }
            return response()->json($products);
        } else {

            $products = $products->where("products.warehouse_id", $warehouse_id)->paginate(50);

            if(!empty($dl)){
                $tmp            = $productsExport;
                $fn             = 'products_'.time();

                foreach($tmp as $p){
                    $totalStockIn       = DB::table('stock')->where([["product_id", $p->product_id], ["type", 1], ["warehouse_id", $warehouse_id]])->sum("product_amount");
                    $totalStockOut      = DB::table('stock')->where([["product_id", $p->product_id], ["type", 0], ["warehouse_id", $warehouse_id]])->sum("product_amount");
                    $totalRetur         = DB::table('stock')->where([["product_id", $p->product_id], ["type", 2], ["warehouse_id", $warehouse_id]])->sum("product_amount");
                    $availableStock     = ($totalStockIn-$totalStockOut)+$totalRetur;
                    $p->product_amount  = $availableStock;
                }

                $productExport  = [];

                $i = 1;
                foreach($tmp as $t){
                    if($hargaBeli == "on" && $hargaJual != "on"){
                        $productExport[] = [
                            "NO."                   => $i,
                            "KODE PRODUK"           => $t->product_code,
                            "NAMA PRODUK"           => $t->product_name,
                            "KATEGORI"              => $t->category_name,
                            "JUMLAH"                => $t->product_amount,
                            "HARGA BELI (RP)"       => $t->purchase_price,
                        ];
                    }
                    if($hargaBeli != "on" && $hargaJual == "on"){
                        $productExport[] = [
                            "NO."                   => $i,
                            "KODE PRODUK"           => $t->product_code,
                            "NAMA PRODUK"           => $t->product_name,
                            "KATEGORI"              => $t->category_name,
                            "JUMLAH"                => $t->product_amount,
                            "HARGA JUAL (RP)"       => $t->sale_price,
                        ];
                    }

                    if($hargaBeli != "on" && $hargaJual != "on"){
                        $productExport[] = [
                            "NO."                   => $i,
                            "KODE PRODUK"           => $t->product_code,
                            "NAMA PRODUK"           => $t->product_name,
                            "KATEGORI"              => $t->category_name,
                            "JUMLAH"                => $t->product_amount,
                        ];
                    }

                    if($hargaBeli == "on" && $hargaJual == "on"){
                        $productExport[] = [
                            "NO."                   => $i,
                            "KODE PRODUK"           => $t->product_code,
                            "NAMA PRODUK"           => $t->product_name,
                            "KATEGORI"              => $t->category_name,
                            "JUMLAH"                => $t->product_amount,
                            "HARGA BELI (RP)"       => $t->purchase_price,
                            "HARGA JUAL (RP)"       => $t->sale_price,
                        ];
                    }

                    $i++;
                }
                $heading = array_keys($productExport[0]);

                if($dl == "xls"){
                    return (new ProductsExport($heading, $productExport))->download($fn.'.xls', \Maatwebsite\Excel\Excel::XLS);
                } else if($dl == "pdf"){
                    $pdf            = PDF::loadView('download_products', compact("heading","productExport"));
                    $fn             = $fn.".pdf";

                    return $pdf->setPaper('A4', 'landscape')->stream($fn);
                }
            } else {
                foreach($products as $p){
                    $totalStockIn       = DB::table('stock')->where([["product_id", $p->product_id], ["type", 1], ["warehouse_id", $warehouse_id]])->sum("product_amount");
                    $totalStockOut      = DB::table('stock')->where([["product_id", $p->product_id], ["type", 0], ["warehouse_id", $warehouse_id]])->sum("product_amount");
                    $totalRetur         = DB::table('stock')->where([["product_id", $p->product_id], ["type", 2], ["warehouse_id", $warehouse_id]])->sum("product_amount");
                    $availableStock     = ($totalStockIn-$totalStockOut)+$totalRetur;
                    $p->product_amount  = $availableStock;
                }

                $stockData = DB::table('stock')
                ->select(
                    'product_id',
                    DB::raw('SUM(CASE WHEN type = 1 THEN product_amount ELSE 0 END) as totalStockIn'),
                    DB::raw('SUM(CASE WHEN type = 0 THEN product_amount ELSE 0 END) as totalStockOut'),
                    DB::raw('SUM(CASE WHEN type = 2 THEN product_amount ELSE 0 END) as totalRetur')
                )
                ->where('warehouse_id', $warehouse_id)
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

                foreach ($productsExport as $p) {
                    $stock = $stockData[$p->product_id] ?? null;

                    $totalStockIn       = $stock->totalStockIn ?? 0;
                    $totalStockOut      = $stock->totalStockOut ?? 0;
                    $totalRetur         = $stock->totalRetur ?? 0;

                    $availableStock     = ($totalStockIn - $totalStockOut) + $totalRetur;
                    $total_modal        += $availableStock * $p->purchase_price;
                    $total_all_stock    += $availableStock;
                }
            }

            return View::make("products")->with(compact("products", "warehouse", "total_modal", "total_all_stock"));
        }
    }

    public function services(Request $req){
        $search         = $req->q;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $services = DB::table('services')
                    ->select("services.*")
                    ->where("services.warehouse_id", $warehouse_id);

        if(!empty($search)){
            $services = $services->orWhere("services.nama_jasa", "LIKE", "%".$search."%");
        }

        $warehouse = $this->getWarehouse();

        if($req->format == "json"){
            $service_id = $req->service_id;

            if(!empty($service_id)){
                $services = $services->where([["service_id", $service_id],["NA", "N"]])->first();
                $services->biaya = number_format($services->biaya, 0, ",", ".");
            } else {
                $services = $services->where("NA", "N")->orderBy("nama_jasa", "ASC")->get();
            }
            return response()->json($services);
        } else {

            $services = $services->where("NA", "N")->orderBy("nama_jasa", "ASC")->paginate(50);

            return View::make("services")->with(compact("services", "warehouse"));
        }
    }

    public function services_save(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $req->validate([
            'nama_jasa'     => 'required',
            'biaya'         => 'required|numeric',

        ],
        [
            'nama_jasa.required'        => 'Nama Jasa Servis belum diisi!',
            'purchase_price.required'   => 'Biaya untuk Jasa belum diisi!',
            'purchase_price.numeric'    => 'Biaya untuk Jasa berupa angka!',
        ]);

        $data = [
            "user_id"           => Auth::user()->id,
            "warehouse_id"      => $warehouse_id,
            "nama_jasa"         => $req->nama_jasa,
            "biaya"             => $req->biaya,
        ];

        if(empty($req->id)){
            $add = DB::table('services')->insertGetId($data);

            if($add){
                $req->session()->flash('success', "Jasa Servis berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "Jasa Servis gagal ditambahkan!");
            }
        } else {
            $update = DB::table('services')->where("service_id", $req->id)->update($data);

            if($update){
                $req->session()->flash('success', "Jasa Servis berhasil diubah.");
            } else {
                $req->session()->flash('error', "Jasa Servis gagal diubah!");
            }
        }

        return redirect()->back();
    }

    public function services_delete(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $del = DB::table('services')->where([["service_id", $req->id], ["warehouse_id", $warehouse_id]])->update(["NA" => "Y"]);

        if($del){
            $req->session()->flash('success', "Jasa Servis berhasil dihapus.");
        } else {
            $req->session()->flash('error', "Jasa Serivs gagal dihapus!");
        }

        return redirect()->back();
    }

    public function products_wip(Request $req){
        $search = $req->q;
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $products = DB::table('products_wip')
                    ->leftJoin("products", "products_wip.product_id", "=", "products.product_id")
                    ->select("products_wip.*", "products.*");

        if(!empty($search)){
            $products = $products->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["status", 0], ["products.warehouse_id", $warehouse_id]])
                        ->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["status", 0], ["status", 0], ["products.warehouse_id", $warehouse_id]]);
        }

        $products = $products->where([["products_wip.status", 0], ["products_wip.warehouse_id", $warehouse_id]])->orderBy("products_wip.product_wip_id", "desc")->paginate(50);

        $warehouse = $this->getWarehouse();
        return View::make("products_wip")->with(compact("products", "warehouse"));
    }

    public function products_wip_history(Request $req){
        $search = $req->q;
        $dl     = $req->dl;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $products = DB::table('products_wip')
                    ->leftJoin("products", "products_wip.product_id", "=", "products.product_id")
                    ->select("products_wip.*", "products.*");

        if(!empty($search)){
            $products = $products->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["status", 1]])
                        ->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["status", 1]]);
        }

        $products = $products->where([["products_wip.status", 1], ["products_wip.warehouse_id", $warehouse_id]])->orderBy("products_wip.date_out", "desc");

        $warehouse = $this->getWarehouse();

        if(!empty($dl)){
            $tmp            = $products->orderBy("products_wip.product_wip_id", "asc")->get();
            $fn             = 'wip_history_'.time();
            $historyExport  = [];

            foreach($tmp as $t){
                $historyExport[] = [
                    "KODE PRODUK"       => $t->product_code,
                    "NAMA PRODUK"       => $t->product_name,
                    "JUMLAH"            => $t->product_amount,
                    "TANGGAL MASUK"     => date('d/m/Y', strtotime($t->date_in)),
                    "TANGGAL KELUAR"    => date('d/m/Y', strtotime($t->date_out)),
                ];
            }

            if($dl == "xls"){
                return (new WIPHistoryExport($historyExport))->download($fn.'.xls', \Maatwebsite\Excel\Excel::XLS);
            } else if($dl == "pdf"){
                return (new WIPHistoryExport($historyExport))->download($fn.'.pdf');
            }
        }

        $products = $products->paginate(50);

        return View::make("products_wip_history")->with(compact("products", "warehouse"));
    }

    public function product_check(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $product = DB::table('products')->where([["product_code", $req->pcode], ["warehouse_id", $warehouse_id]])->select("product_id", "product_code","product_name")->first();

        $result = ["status" => 0, "data" => null];

        if(!empty($product)){
            $result = ["status" => 1, "data" => $product];
        }

        return response()->json($result);
    }

    public function nopol_check(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $nopol = DB::table('orders')->where([["kendaraan_nopol", $req->nopol], ["warehouse_id", $warehouse_id]])->select("customer_name", "customer_nohp","customer_alamat", "kendaraan_jenis", "kendaraan_km", "kendaraan_tahun")->orderBy("order_id", "desc")->first();

        $result = ["status" => 0, "data" => null];

        if(!empty($nopol)){
            $result = ["status" => 1, "data" => $nopol];
        }

        return response()->json($result);
    }

    public function product_save(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $req->validate([
            'product_code'      => 'required|unique:products,product_code,'.$req->id.',product_id,warehouse_id,'.$warehouse_id,
            'product_name'      => 'required',
            'purchase_price'    => 'required|numeric',
            'sale_price'        => 'nullable|numeric',
            'category'          => 'required|exists:categories,category_id',

        ],
        [
            'product_code.required'     => 'Product Code belum diisi!',
            'product_code.unique'       => 'Product Code telah digunakan!',
            'product_name.required'     => 'Product Name belum diisi!',
            'purchase_price.required'   => 'Purchase Price belum diisi!',
            'purchase_price.numeric'    => 'Purchase Price harus berupa angka!',
            'sale_price.numeric'        => 'Sale Price harus berupa angka!',
            'category.required'         => 'Kategori belum dipilih!',
            'category.exists'           => 'Kategori tidak tersedia!',
        ]);

        $data = [
            "user_id"           => Auth::user()->id,
            "warehouse_id"      => $warehouse_id,
            "product_code"      => $req->product_code,
            "product_name"      => $req->product_name,
            "purchase_price"    => $req->purchase_price,
            "sale_price"        => $req->sale_price,
            "category_id"       => $req->category,
        ];

        if(empty($req->id)){
            $add = DB::table('products')->insertGetId($data);

            if($add){
                $req->session()->flash('success', "Product berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "Product gagal ditambahkan!");
            }
        } else {
            $update = DB::table('products')->where("product_id", $req->id)->update($data);

            if($update){
                $req->session()->flash('success', "Product berhasil diubah.");
            } else {
                $req->session()->flash('error', "Product gagal diubah!");
            }
        }

        return redirect()->back();
    }

    public function product_import(Request $req){
		$this->validate($req, [
			'file' => 'required|mimes:csv,xls,xlsx'
        ],
        [
            "file.required" => "File belum dipilih!",
            "file.mimes"    => "File harus dalam format CSV/XLS/XLSX!"
        ]);

		$file = $req->file('file');

		$filename = rand()."-".$file->getClientOriginalName();

		$file->move('upload/import',$filename);

		$import = Excel::toArray(new ProductsImport, public_path('upload/import/'.$filename));

        $data = [];
        foreach($import as $value){
            foreach($value as $v){
                $data[]=$v;
            }
        }

        $doneImport = 0;
        $countImport = count($data);
        foreach($data as $d){
            $checkExists = DB::table('products')
                        ->where("product_code", $d["KODE PRODUK"])
                        ->get()
                        ->count();

            if($checkExists == 0){
                if(Session::has('selected_warehouse_id')){
                    $warehouse_id = Session::get('selected_warehouse_id');
                } else {
                    $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
                }

                $param = [
                    'user_id'           => Auth::user()->id,
                    'warehouse_id'      => $warehouse_id,
                    'product_code'      => $d['KODE PRODUK'],
                    'product_name'      => $d['NAMA PRODUK'],
                    'purchase_price'    => $d['HARGA PEMBELIAN'],
                    'sale_price'        => $d['HARGA SATUAN'],
                ];

                $add = DB::table('products')->insertOrIgnore($param);

                if($add){
                    $doneImport++;
                }
            }
        }

        if($doneImport == $countImport){
            $req->session()->flash('success', "Semua data berhasil diimport.");
        } else {
            if($doneImport > 0){
                $req->session()->flash('success', "Sebagian data berhasil diimport.");
            } else {
                $req->session()->flash('error', "Data gagal diimport!");
            }
        }

		return redirect()->back();
    }

    public function product_wip_save(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $req->validate([
            'product_code'      => 'required|exists:products,product_code',
            'pamount'           => 'required|numeric',

        ],
        [
            'product_code.required' => 'Product Code belum diisi!',
            'product_code.exists'   => 'Product Code tidak ditemukan!',
            'pamount.required'      => 'Amount belum diisi!',
            'pamount.numeric'       => 'Amount harus berupa angka!',
        ]);

        $product_id = DB::table('products')
                        ->where([["product_code", $req->product_code], ["warehouse_id", $warehouse_id]])
                        ->select("product_id")
                        ->first();

        if(!empty($req->wip_date)){
            $req->validate([
                'wip_date'          => 'date_format:m/d/Y H:i:s',

            ],
            [
                'wip_date.date_format'      => 'Format tanggal salah! Format: BLN/TGL/THN JAM:MNT:DTK.',
            ]);
            $date_in = date('Y-m-d H:i:s', strtotime($req->wip_date));
        } else {
            $date_in = date("Y-m-d H:i:s");
        }

        if(!empty($product_id)){
            $product_id = $product_id->product_id;
            $data = [
                "product_id"        => $product_id,
                "warehouse_id"      => $warehouse_id,
                "product_amount"    => $req->pamount,
                "date_in"           => $date_in
            ];

            $add = DB::table('products_wip')->insertGetId($data);

            if($add){
                $req->session()->flash('success', "WIP berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "WIP gagal ditambahkan!");
            }
        } else {
            $req->session()->flash('error', "Product tidak ditemukan!");
        }

        return redirect()->back();
    }

    public function product_delete(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $del = DB::table('products')->where([["product_id", $req->id], ["warehouse_id", $warehouse_id]])->delete();

        if($del){
            $stock_id = DB::table('stock')->where([["product_id", $req->id], ["warehouse_id", $warehouse_id]])->first();
            if(!empty($stock_id)){
                $stock_id = $stock_id->stock_id;
                DB::table('stock')->where([["product_id", $req->id], ["warehouse_id", $warehouse_id]])->delete();
                DB::table('history')->where([["stock_id", $stock_id], ["warehouse_id", $warehouse_id]])->delete();
            }
            $req->session()->flash('success', "Product berhasil dihapus.");
        } else {
            $req->session()->flash('error', "Product gagal dihapus!");
        }

        return redirect()->back();
    }

    public function product_wip_delete(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $del = DB::table('products_wip')->where([["product_wip_id", $req->id], ["warehouse_id", $warehouse_id]])->delete();

        if($del){
            $req->session()->flash('success', "Product berhasil dihapus.");
        } else {
            $req->session()->flash('error', "Product gagal dihapus!");
        }

        return redirect()->back();
    }

    public function product_wip_complete(Request $req){
        $wip_id     = $req->wip_id;
        $amount     = $req->amount;
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $req->validate([
            'wip_id'    => 'required|exists:products_wip,product_wip_id',
            'amount'    => 'required|numeric',

        ],
        [
            'wip_id.required'   => 'WIP ID tidak ditemukan!',
            'wip_id.exists'     => 'WIP ID tidak ditemukan!',
            'amount.required'   => 'Amount belum diisi!',
            'amount.numeric'    => 'Amount harus berupa angka!',
        ]);

        $wip        = DB::table('products_wip')->select("*")->where([["product_wip_id", $wip_id], ["warehouse_id", $warehouse_id]])->first();

        if($amount <= $wip->product_amount){
            $wipComplete = null;

            if(count(array($wip)) > 0){
                $data = new Request([
                    "product_id"    => $wip->product_id,
                    "warehouse_id"  => $warehouse_id,
                    "amount"        => $amount,
                    "type"          => 1,
                ]);

                $wipComplete = $this->product_stock($data);
            }

            if($wipComplete){
                if($amount == $wip->product_amount){
                    $data = [
                        "date_out"  => date('Y-m-d H:i:s'),
                        "status"    => 1,
                    ];
                    DB::table('products_wip')->where([["product_wip_id", $wip_id], ["warehouse_id", $warehouse_id]])->update($data);
                } else {
                    $data = [
                        "product_id"        => $wip->product_id,
                        "warehouse_id"      => $warehouse_id,
                        "product_amount"    => $amount,
                        "status"            => 1,
                    ];
                    $insertNew = DB::table('products_wip')->insertGetId($data);

                    if($insertNew){
                        $curAmount = $wip->product_amount - $amount;
                        DB::table('products_wip')->where([["product_wip_id", $wip_id], ["warehouse_id", $warehouse_id]])->update(["product_amount" => $curAmount]);
                    }
                }
                $req->session()->flash('success', "Product telah dipindahkan ke Products List.");
            } else {
                $req->session()->flash('error', "Terjadi kesalahan! Mohon coba kembali!");
            }
        } else {
            $req->session()->flash('error', "Amount tidak tersedia!");
        }
        return redirect()->back();
    }

    public function product_wip_import(Request $req){
		$this->validate($req, [
			'file' => 'required|mimes:csv,xls,xlsx'
        ],
        [
            "file.required" => "File belum dipilih!",
            "file.mimes"    => "File harus dalam format CSV/XLS/XLSX!"
        ]);

		$file = $req->file('file');

		$filename = rand()."-".$file->getClientOriginalName();

		$file->move('upload/import',$filename);

		$import = Excel::toArray(new ProductsImport, public_path('upload/import/'.$filename));

        $data = [];
        foreach($import as $value){
            foreach($value as $v){
                $data[]=$v;
            }
        }

        $doneImport = 0;
        $countImport = count($data);
        foreach($data as $d){
            $product = DB::table('products')
                        ->where("product_code", $d["KODE PRODUK"])
                        ->first();

            if(!empty($product)){
                if(Session::has('selected_warehouse_id')){
                    $warehouse_id = Session::get('selected_warehouse_id');
                } else {
                    $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
                }

                if(empty($d['TANGGAL MASUK'])){
                    $date_in = date("Y-m-d H:i:s");
                } else {
                    $date_in = date("Y-m-d H:i:s", strtotime($d['TANGGAL MASUK']));
                }

                $param = [
                    'warehouse_id'      => $warehouse_id,
                    'product_id'        => $product->product_id,
                    'product_amount'    => $d['JUMLAH'],
                    'date_in'           => $date_in,
                ];

                $add = DB::table('products_wip')->insertOrIgnore($param);

                if($add){
                    $doneImport++;
                }
            }
        }

        if($doneImport == $countImport){
            $req->session()->flash('success', "Semua data berhasil diimport.");
        } else {
            if($doneImport > 0){
                $req->session()->flash('success', "Sebagian data berhasil diimport.");
            } else {
                $req->session()->flash('error', "Data gagal diimport!");
            }
        }

		return redirect()->back();
    }

    public function product_stock(Request $req){
        $product_id = $req->product_id;
        $amount     = $req->amount;
        $stockDate  = date("Y-m-d H:i:s");
        $type       = $req->type;
        $no_nota    = $req->no_nota;
        $customer   = $req->customer;

        if(empty($amount)){
            $amount = 1;
        }

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        if(!empty($amount)){
            $data = [
                "user_id"           => Auth::user()->id,
                "warehouse_id"      => $warehouse_id,
                "product_id"        => $product_id,
                "product_amount"    => $amount,
                "type"              => $type,
                "no_nota"           => $no_nota,
                "customer"          => $customer,
            ];

            if(!empty($stockDate)){
                $data["datetime"] = date("Y-m-d H:i:s", strtotime($stockDate));
            } else {
                $data["datetime"] = date("Y-m-d H:i:s");
            }

            $totalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 1]])->sum("product_amount");
            $totalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 0]])->sum("product_amount");
            $totalRetur     = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 2]])->sum("product_amount");
            $availableStock = ($totalStockIn-$totalStockOut)+$totalRetur;

            $endingTotalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 1]])->sum("product_amount");
            $endingTotalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 0]])->sum("product_amount");
            $endingTotalRetur     = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 2]])->sum("product_amount");
            $endingAmount = ($endingTotalStockIn-$endingTotalStockOut)+$endingTotalRetur;

            if($type == 0){
                if($amount > $availableStock){
                    $result = ["status" => 0, "message" => "Jumlah stock out melebihi jumlah stock yang tersedia!"];
                    goto resp;
                } else {
                    $data["ending_amount"] = $endingAmount-$amount;
                }
            } else {
                $data["ending_amount"] = $endingAmount+$amount;
            }

            $updateStock = DB::table('stock')->insertGetId($data);

            if($updateStock){
                $result = ["status" => 1, "message" => "Stok berhasil diupdate."];
            } else {
                $result = ["status" => 0, "message" => "Stok gagal diupdate! Mohon coba kembali!"];
            }
        } else {
            $result = ["status" => 0, "message" => "Amount belum diisi!"];
        }

        resp:
        return response()->json($result);
    }

    public function product_stock_history(Request $req){
        $search = $req->search;
        $dl     = $req->dl;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $history = DB::table('stock')
                    ->leftJoin("products", "stock.product_id", "=", "products.product_id")
                    ->leftJoin("users", "stock.user_id", "=", "users.id")
                    ->select("stock.*", "products.product_code", "products.product_name", "products.sale_price", "users.name");

        if(!empty($search)){
            $history = $history->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]])
                        ->orWhere([["stock.stock_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]])
                        ->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]]);
        } else {
            $history = $history->where("products.warehouse_id", $warehouse_id);
        }

        if(!empty($dl)){
            $tmp            = $history->orderBy("stock.stock_id", "asc")->get();
            $fn             = 'history_'.time();
            $historyExport  = [];

            foreach($tmp as $t){
                if($t->type == "0"){
                    $in     = "";
                    $out    = $t->product_amount;
                    $retur  = "";
                } else if($t->type == "1"){
                    $in     = $t->product_amount;
                    $out    = "";
                    $retur  = "";
                } else {
                    $in     = "";
                    $out    = "";
                    $retur  = $t->product_amount;
                }

                $historyExport[] = [
                    "DATE"              => date('d/m/Y', strtotime($t->datetime)),
                    "PRODUCT"           => $t->product_name,
                    "NO. NOTA"          => $t->no_nota,
                    "CUSTOMER"          => $t->customer,
                    "STOCK IN"          => $in,
                    "STOCK OUT"         => $out,
                    "RETUR"             => $retur,
                    "SISA"              => $t->ending_amount,
                    "SATUAN (RP)"       => number_format($t->sale_price, 2, ",","."),
                ];
            }

            if($dl == "xls"){
                return (new HistoryExport($historyExport))->download($fn.'.xls', \Maatwebsite\Excel\Excel::XLS);
            } else if($dl == "pdf"){
                return (new HistoryExport($historyExport))->download($fn.'.pdf');
            }
        }

        $history = $history->orderBy("stock.stock_id", "desc")->paginate(50);

        $warehouse = $this->getWarehouse();
        return View::make("stock_history")->with(compact("history", "warehouse"));
    }

    public function categories(Request $req){
        $search = $req->q;
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $categories = DB::table('categories')->select("*");

        if(!empty($search)){
            $categories = $categories->where([["category_name", "LIKE", "%".$search."%"], ["warehouse_id", $warehouse_id]]);
        }

        if($req->format == "json"){
            $categories = $categories->where("warehouse_id", $warehouse_id)->get();

            return response()->json($categories);
        } else {
            $categories = $categories->where("warehouse_id", $warehouse_id)->paginate(50);
            $warehouse = $this->getWarehouse();
            return View::make("categories")->with(compact("categories", "warehouse"));
        }
    }

    public function categories_save(Request $req){
        $category_id = $req->category_id;
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $req->validate([
            'category_name'      => ['required']

        ],
        [
            'category_name.required'     => 'Nama Kategori belum diisi!',
        ]);

        $data = [
            "warehouse_id"       => $warehouse_id,
            "category_name"      => $req->category_name
        ];

        if(empty($category_id)){
            $add = DB::table('categories')->insertGetId($data);

            if($add){
                $req->session()->flash('success', "Kategori baru berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "Kategori baru gagal ditambahkan!");
            }
        } else {
            $edit = DB::table('categories')->where([["category_id", $category_id], ["warehouse_id", $warehouse_id]])->update($data);

            if($edit){
                $req->session()->flash('success', "Kategori berhasil diubah.");
            } else {
                $req->session()->flash('error', "Kategori gagal diubah!");
            }
        }

        return redirect()->back();
    }

    public function categories_delete(Request $req){
        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $del = DB::table('categories')->where([["category_id", $req->delete_id], ["warehouse_id", $warehouse_id]])->delete();

        if($del){
            DB::table('products')->where([["category_id", $req->delete_id], ["warehouse_id", $warehouse_id]])->update(["category_id" => null]);
            $req->session()->flash('success', "Kategori berhasil dihapus.");
        } else {
            $req->session()->flash('error', "Kategori gagal dihapus!");
        }

        return redirect()->back();
    }

    public function city(Request $req){
        $search = $req->q;

        $city = DB::table('city');

        if(!empty($search)){
            $city = $city->where([["city_name", "LIKE", "%".$search."%"]]);
        }

        if($req->format == "json"){
            $city = $city->get();

            return response()->json($city);
        } else {
            $city = $city->paginate(20);
            return View::make("city")->with(compact("city"));
        }
    }

    public function city_save(Request $req){
        $city_id = $req->city_id;

        $req->validate([
            'city_name'      => ['required']

        ],
        [
            'city_name.required'     => 'Nama Kota belum diisi!',
        ]);

        $data = [
            "city_id"       => $city_id,
            "city_name"     => $req->city_name
        ];

        if(empty($city_id)){
            $add = DB::table('city')->insertGetId($data);

            if($add){
                $req->session()->flash('success', "Kota baru berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "Kota baru gagal ditambahkan!");
            }
        } else {
            $edit = DB::table('city')->where("city_id", $city_id)->update($data);

            if($edit){
                $req->session()->flash('success', "Kota berhasil diubah.");
            } else {
                $req->session()->flash('error', "Kota gagal diubah!");
            }
        }

        return redirect()->back();
    }

    public function city_delete(Request $req){
        $del = DB::table('city')->where("city_id", $req->delete_id)->delete();

        if($del){
            DB::table('city')->where("city_id", $req->delete_id)->update(["city_id" => null]);
            $req->session()->flash('success', "Kota berhasil dihapus.");
        } else {
            $req->session()->flash('error', "Kota gagal dihapus!");
        }

        return redirect()->back();
    }

    public function generateBarcode(Request $req){
        $code       = $req->code;
        $print      = $req->print;
        $barcodeB64 = DNS1D::getBarcodePNG("".$code."", 'C128', 2, 81, array(0,0,0), true);
        $barcode    = $barcodeB64;
        $jumlah     = $req->jumlah;

        if(empty($jumlah)){
            $jumlah = 1;
        }

        if(!empty($print) && $print == true){
            return View::make("barcode_print")->with(compact("barcode", "jumlah"));
        } else {
            $barcode    = base64_decode($barcodeB64);
            $image      = imagecreatefromstring($barcode);
            $barcode    = imagepng($image);
            imagedestroy($image);

            return response($barcode)->header('Content-type','image/png');
        }
    }

    public function warehouse(Request $req){
        $search = $req->q;

        $warehouse = DB::table('warehouse')
                        ->leftJoin("city", "city.city_id", "=", "warehouse.city_id");

        if(!empty($search)){
            $warehouse = $warehouse->where("username", "LIKE", "%".$search."%")
                        ->orWhere("name", "LIKE", "%".$search."%");
        }

        if($req->format == "json"){
            $warehouse = $warehouse->get();

            return response()->json($warehouse);
        } else {
            $warehouse = $warehouse->paginate(50);

            return View::make("warehouse")->with(compact("warehouse"));
        }
    }

    public function getWarehouse(){
        if(Auth::user()->role == 0){
            $warehouse = DB::table('warehouse')->get();
        } else {
            $warehouse = DB::table('warehouse')->where("city_id", Auth::user()->city_id)->get();
        }

        return $warehouse;
    }

    public function warehouse_select(Request $req){
        $req->validate([
            'warehouse_id'          => 'exists:warehouse,warehouse_id',

        ],
        [
            'warehouse_id.exists'   => 'Warehouse tidak ditemukan!',
        ]);

        $warehouse = DB::table('warehouse')->where("warehouse_id", $req->warehouse_id)->first();
        if(!empty($warehouse)){
            $req->session()->put('selected_warehouse_id', $req->warehouse_id);
            $req->session()->put('selected_warehouse_name', $warehouse->warehouse_name);
        }
        return redirect()->back();
    }

    public function warehouse_save(Request $req){
        $warehouse_id = $req->warehouse_id;

        $req->validate([
            'name'      => 'required',
            'address'   => 'required',
            'nohp'      => 'required',
            'city'      => 'required|exists:city,city_id',

        ],
        [
            'name.required'     => 'Nama Warehouse belum diisi!',
            'address.required'  => 'Alamat Warehouse belum diisi!',
            'nohp.required'     => 'No. HP/Telp. Warehouse belum diisi!',
            'city.required'     => 'Kota belum dipilih!',
            'city.exists'       => 'Kota tidak tersedia!',
        ]);

        $data = [
            "warehouse_name"    => $req->name,
            "warehouse_address" => $req->address,
            "warehouse_nohp"    => $req->nohp,
            "city_id"           => $req->city,
        ];

        if(empty($warehouse_id)){
            $add = DB::table('warehouse')->insertGetId($data);

            if($add){
                $req->session()->flash('success', "Warehouse baru berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "warehouse baru gagal ditambahkan!");
            }
        } else {
            $edit = DB::table('warehouse')->where("warehouse_id", $warehouse_id)->update($data);

            if($edit){
                $req->session()->flash('success', "Warehouse berhasil diubah.");
            } else {
                $req->session()->flash('error', "Warehouse gagal diubah!");
            }
        }

        return redirect()->back();
    }

    public function warehouse_delete(Request $req){
        $del = DB::table('warehouse')->where("warehouse_id", $req->delete_id)->delete();

        if($del){
            $req->session()->flash('success', "Warehouse berhasil dihapus.");
        } else {
            $req->session()->flash('error', "Warehouse gagal dihapus!");
        }

        return redirect()->back();
    }

    public function cekStok($product_id)
    {
        $totalStockIn   = DB::table('stock')->where([["product_id", $product_id], ["type", 1]])->sum("product_amount");
        $totalStockOut  = DB::table('stock')->where([["product_id", $product_id], ["type", 0]])->sum("product_amount");
        $totalRetur     = DB::table('stock')->where([["product_id", $product_id], ["type", 2]])->sum("product_amount");
        $availableStock = ($totalStockIn-$totalStockOut)+$totalRetur;

        $result = ["stock" => $availableStock];

        return $result;
    }

    public function doStockIn(Request $req){
        $product_id = $req->product_id;
        $amount = $req->amount;


        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        if(empty($req->pembelian)){
            $req->pembelian = "N";
        }

        $tgl_masuk = $req->tgl_masuk;

        if(empty($tgl_masuk)){
            $tgl_masuk = date("Y-m-d H:i:s");
        }

        $endingTotalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 1]])->sum("product_amount");
        $endingTotalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 0]])->sum("product_amount");
        $endingTotalRetur     = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 2]])->sum("product_amount");
        $endingAmount         = ($endingTotalStockIn-$endingTotalStockOut)+$endingTotalRetur;

        $data = [
            "datetime"          => $tgl_masuk,
            "product_id"        => $product_id,
            "product_amount"    => $amount,
            "type"              => 1,
            "user_id"           => Auth::user()->id,
            "warehouse_id"      => $warehouse_id,
            "ending_amount"     => $endingAmount
        ];

        $add = DB::table('stock')->insertGetId($data);

        if($add){
            return true;
        } else {
            return false;
        }
    }

    public function doStockOut(Request $req){
        $product_id = $req->product_id;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $endingTotalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 1]])->sum("product_amount");
        $endingTotalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 0]])->sum("product_amount");
        $endingTotalRetur     = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 2]])->sum("product_amount");
        $endingAmount         = ($endingTotalStockIn-$endingTotalStockOut)+$endingTotalRetur;

        $data = [
            "datetime"          => date("Y-m-d H:i:s"),
            "product_id"        => $product_id,
            "product_amount"    => $req->amount,
            "type"              => 0,
            "pembelian"         => "Y",
            "user_id"           => Auth::user()->id,
            "warehouse_id"      => $warehouse_id,
            "ending_amount"     => $endingAmount
        ];

        $add = DB::table('stock')->insertGetId($data);

        if($add){
            return true;
        } else {
            return false;
        }
    }

    public function order(Request $req)
    {
        $order_id = $req->id;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $warehouse = $this->getWarehouse();

        if(empty($order_id)){
            $order      = DB::table("orders")->where([["status", 0], ["warehouse_id", $warehouse_id]])->first();
            if(empty($order)){
                $data = [
                    "warehouse_id"  => $warehouse_id,
                    "user_id"       => Auth::user()->id,
                ];

                $add = DB::table('orders')->insertGetId($data);

                $order = DB::table("orders")->where("status", 0)->first();
            } else {
                $order_id   = $order->order_id;
            }


            $detail = DB::table("orders_detail")
                        ->select("products.product_name", "products.product_code", "products.sale_price", "orders_detail.*",)
                        ->leftJoin("orders", "orders.order_id", "orders_detail.order_id")
                        ->leftJoin("products", "products.product_id", "orders_detail.product_id")
                        ->leftJoin("categories", "categories.category_id", "products.category_id")
                        ->where([["orders.order_id", $order_id], ["orders_detail.type", 0], ["orders.status", 0]])->orderBy("orders.order_id", "asc");

            $detail = DB::table("orders_detail")
                        ->select("services.nama_jasa as product_name", "services.nama_jasa as product_code", "services.biaya as sale_price", "orders_detail.*")
                        ->leftJoin("orders", "orders.order_id", "orders_detail.order_id")
                        ->leftJoin("services", "services.service_id", "orders_detail.product_id")
                        ->where([["orders.order_id", $order_id], ["orders_detail.type", 1], ["orders.status", 0]])->orderBy("orders.order_id", "asc")
                        ->union($detail)
                        ->get();
        } else {
            $order = DB::table("orders")->where("order_id", $order_id)->first();
            $order_id   = $order->order_id;
            if($order){
                $detail = DB::table("orders_detail")
                            ->select("products.product_name", "products.product_code", "orders_detail.price as sale_price", "products.purchase_price as purchase_price", "orders_detail.*",)
                            ->leftJoin("orders", "orders.order_id", "orders_detail.order_id")
                            ->leftJoin("products", "products.product_id", "orders_detail.product_id")
                            ->leftJoin("categories", "categories.category_id", "products.category_id")
                            ->where([["orders_detail.type", 0], ["orders.order_id", $order_id]])->orderBy("orders.order_id", "asc");

                $detail = DB::table("orders_detail")
                            ->select("services.nama_jasa as product_name", "services.nama_jasa as product_code", "orders_detail.price as sale_price", "orders_detail.price as purchase_price", "orders_detail.*")
                            ->leftJoin("orders", "orders.order_id", "orders_detail.order_id")
                            ->leftJoin("services", "services.service_id", "orders_detail.product_id")
                            ->where([["orders_detail.type", 1], ["orders.order_id",$order_id]])->orderBy("orders.order_id", "asc")
                            ->union($detail)
                            ->get();
            } else {
                $req->session()->flash('error', "Order tidak ditemukan!");
                return redirect()->route("order.list");
            }
        }

        //return response()->json($detail);
        $total_harga    = 0;
        $total_harga2   = 0;
        $total          = 0;
        foreach($detail as $d){
            $total_harga    += ($d->sale_price*$d->amount);
            if(!empty($d->purchase_price)){
                $total_harga2   += $d->purchase_price*$d->amount;
            }
        }

        if(!empty($order->diskon)){
            $total_harga -= $order->diskon;
        }

        DB::table('orders')->where("order_id", $order_id)->update(["tgl_order" => date("Y-m-d H:i:s"), "total_harga" => $total_harga, "total_harga2" => $total_harga2]);

        return View::make('order')->with(compact("order", "detail", "total_harga", "warehouse"));
    }

    public function order_save(Request $req){
        $order_id           = $req->order_id;
        $type               = $req->type;
        $amount             = $req->amount;

        if($type == 0){
            $product_id         = $req->product;
            $req->validate([
                'product_id'        => 'exists:products,product_id',
                'amount'            => 'required|numeric',

            ],
            [
                'product_id.exists'     => 'Item tidak tersedia!',
                'amount.required'       => 'Jumlah belum diisi!',
                'amount.numeric'        => 'Jumlah harus angka!',
                'amount.min'            => 'Jumlah minimal 1!',
            ]);
        } else {
            $product_id         = $req->jasa;
        }

        $req->validate([
            'order_id'          => 'required|exists:orders,order_id',

        ],
        [
            'order_id.required'     => 'Terjadi kesalahan saat memproses permintaan Anda! Mohon coba lagi! (Error Code: 1)',
            'order_id.exists'       => 'Terjadi kesalahan saat memproses permintaan Anda! Mohon coba lagi! (Error Code: 2)',
        ]);

        $price      = null;


        if($type == 0){
            $product    = DB::table("products")->where("product_id", $product_id)->first();

            if(!empty($product)){
                $price = $product->sale_price;
            }

            $productInfo    = $this->cekStok($product_id);
            $stock          = $productInfo["stock"];
        } else {
            $product    = DB::table("services")->where("service_id", $product_id)->first();

            if(!empty($product)){
                $price = $product->biaya;
            }

            $stock          = 1;
            $amount         = 1;
        }

        $exists         = DB::table("orders_detail")->where([["product_id", $product_id], ["order_id", $order_id]])->first();

        if($type == 0){
            $order_type         = $req->orderType2;
            $customer_name      = $req->customer_name2;
            $customer_nohp      = $req->customer_nohp2;
            $alamat             = $req->alamat2;
            $nopol              = $req->nopol2;
            $jenis_kendaraan    = $req->jenis_kendaraan2;
            $km_kendaraan       = $req->km_kendaraan2;
            $tahun_kendaraan    = $req->tahun_kendaraan2;
            $catatan            = $req->catatan2;
        } else {
            $order_type         = $req->orderType3;
            $customer_name      = $req->customer_name3;
            $customer_nohp      = $req->customer_nohp3;
            $alamat             = $req->alamat3;
            $nopol              = $req->nopol3;
            $jenis_kendaraan    = $req->jenis_kendaraan3;
            $km_kendaraan       = $req->km_kendaraan3;
            $tahun_kendaraan    = $req->tahun_kendaraan3;
            $catatan            = $req->catatan3;
        }

        $customerData = [
            "order_type"        => $order_type,
            "customer_name"     => $customer_name,
            "customer_nohp"     => $customer_nohp,
            "customer_alamat"   => $alamat,
            "kendaraan_nopol"   => $nopol,
            "kendaraan_jenis"   => $jenis_kendaraan,
            "kendaraan_km"      => $km_kendaraan,
            "kendaraan_tahun"   => $tahun_kendaraan,
            "catatan"           => $catatan,
        ];

        $updateCustomer = DB::table('orders')->where("order_id", $order_id)->update($customerData);
        if($amount <= $stock){
            if($exists){
                $data = [
                    "type"          => $type,
                    "amount"        => $exists->amount+$amount,
                    "price"         => $price
                ];

                $add = DB::table('orders_detail')->where([["product_id", $product_id], ["order_id", $order_id]])->update($data);
            } else {
                $data = [
                    "order_id"      => $order_id,
                    "type"          => $type,
                    "product_id"    => $product_id,
                    "amount"        => $amount,
                    "price"         => $price
                ];

                $add = DB::table('orders_detail')->insertGetId($data);
            }

            if($add){
                $data = new Request([
                    "product_id"        => $product_id,
                    "amount"            => $amount,
                ]);
                $stockOut = $this->doStockOut($data);
                $req->session()->flash('success', "Item berhasil ditambahkan.");
            } else {
                $req->session()->flash('error', "Item gagal ditambahkan!");
            }
        } else {
            $req->session()->flash('error', "Jumlah melebihi stok yang tersedia! (Stok tersedia: ".$stock.")");
        }

        return redirect()->back();
    }

    public function order_delete(Request $req)
    {
        $detail = DB::table('orders_detail')->where("order_detail_id", $req->delete_id)->first();
        $del    = DB::table('orders_detail')->where("order_detail_id", $req->delete_id)->delete();
        if($del) {

            $data = new Request([
                "product_id"    => $detail->product_id,
                "amount"        => $detail->amount,
            ]);
            $stockOut = $this->doStockIn($data);

            $req->session()->flash('success', "Item berhasil dibatalkan.");
        } else {
            $req->session()->flash('error', "Item gagal dibatalkan!");
        }

        return redirect()->back();
    }

    public function order_repeat(Request $req){
        $order_id   = $req->order_id;

        $order      = DB::table("orders")->where("order_id", $order_id)->first();

        $resp       = [];

        if(!empty($order)){
            $type       = $order->order_type;

            $newData = [
                "order_type"        => $type,
                "tgl_order"         => date("Y-m-d H:i:s"),
                "warehouse_id"      => $order->warehouse_id,
                "user_id"           => Auth::user()->id,
                "customer_name"     => $order->customer_name,
                "customer_nohp"     => $order->customer_nohp,
                "customer_alamat"   => $order->customer_alamat,
                "kendaraan_nopol"   => $order->kendaraan_nopol,
                "kendaraan_jenis"   => $order->kendaraan_jenis,
                "kendaraan_km"      => $order->kendaraan_km,
                "kendaraan_tahun"   => $order->kendaraan_tahun,
                "catatan"           => $order->catatan,
                "status"            => 0,
            ];

            $newOrder   = DB::table('orders')->insertGetId($newData);
            $price      = null;

            $items      = DB::table("orders_detail")->where("order_id", $order_id)->get();

            if($type == 0){
                foreach($items as $i){
                    $product_id = $i->product_id;
                    $product    = DB::table("products")->where("product_id", $product_id)->first();

                    if(!empty($product)){
                        $price = $product->sale_price;
                    }

                    $productInfo    = $this->cekStok($product_id);
                    $stock          = $productInfo["stock"];
                    $amount         = $i->amount;
                }
            } else {
                foreach($items as $i){
                    $product_id = $i->product_id;
                    $product    = DB::table("services")->where("service_id", $product_id)->first();

                    if(!empty($product)){
                        $price = $product->biaya;
                    }

                    $stock          = 1;
                    $amount         = 1;
                }
            }

            foreach($items as $i){
                $product_id = $i->product_id;

                if($amount <= $stock){
                    $data = [
                        "order_id"      => $newOrder,
                        "type"          => $i->type,
                        "product_id"    => $product_id,
                        "amount"        => $amount,
                        "price"         => $price
                    ];

                    $add = DB::table('orders_detail')->insertGetId($data);

                    if($add){
                        if($i->type == 0){
                            $data = new Request([
                                "product_id"        => $product_id,
                                "amount"            => $amount,
                            ]);

                            $stockOut = $this->doStockOut($data);
                        }
                    }
                }
            }
            $resp   = ["status" => 1, "url" => route('order.list')];
        } else {
            $resp   = ["status" => 0, "message" => "Order #".$order_id." tidak ditemukan!"];
        }

        return response()->json($resp);
    }

    public function order_proses(Request $req)
    {
        $customer_name      = $req->customer_name;
        $customer_nohp      = $req->customer_nohp;
        $alamat             = $req->alamat;
        $nopol              = $req->nopol;
        $jenis_kendaraan    = $req->jenis_kendaraan;
        $km_kendaraan       = $req->km_kendaraan;
        $tahun_kendaraan    = $req->tahun_kendaraan;
        $order_type         = $req->orderType;

        if($order_type == 0){
            $req->validate([
                'customer_name' => 'required',
                'customer_nohp' => 'required|numeric',
                'alamat'        => 'required',

            ],
            [
                'customer_name.required'    => 'Nama Customer belum diisi!',
                'customer_nohp.required'    => 'No. HP. belum diisi!',
                'customer_nohp.numeric'     => 'No. HP. harus angka!',
                'alamat.required'           => 'Alamat belum diisi!',
            ]);

            $data = [
                "customer_name" => $customer_name,
                "customer_alamat" => $alamat,
                "order_type"    => $order_type
            ];
        } else {
            $req->validate([
                'nopol'             => 'required',
                'jenis_kendaraan'   => 'required',
                'km_kendaraan'  => 'required',
                'tahun_kendaraan'   => 'required',

            ],
            [
                'nopol.required'                => 'No. Polisi belum diisi!',
                'jenis_kendaraan.required'      => 'Jenis Kendaraan belum diisi!',
                'km_kendaraan.required'         => 'KM Kendaraan belum diisi!',
                'tahun_kendaraan.required'      => 'Tahun Kendaraan belum diisi!',
            ]);

            $data = [
                "kendaraan_nopol"   => $nopol,
                "kendaraan_jenis"   => $jenis_kendaraan,
                "kendaraan_km"      => $km_kendaraan,
                "kendaraan_tahun"   => $tahun_kendaraan,
            ];
        }

        $data["customer_nohp"]  = $customer_nohp;
        $data["status"]         = 1;

        $update    = DB::table('orders')->where("order_id", $req->order_id)->update($data);
        if($update) {
            $req->session()->flash('success', "Order berhasil diproses.");
        } else {
            $req->session()->flash('error', "Order gagal diproses!");
        }

        return redirect()->route("order", ["id" => $req->order_id]);
    }

    public function order_done(Request $req)
    {
        $nama_mekanik   = $req->nama_mekanik;
        $diskon         = $req->diskon;
        $order_type     = $req->order_type;

        if(!empty($nama_mekanik)){
            $update    = DB::table('orders')->where("order_id", $req->order_id)->update(["status" => 2, "nama_mekanik" => $nama_mekanik, "diskon" => $diskon]);
            if($update) {
                $resp   = ["status" => 1, "message" => "Order selesai dan menunggu pembayaran."];
            } else {
                $resp   = ["status" => 0, "message" => "Order gagal diselesaikan!"];
            }
        } else {
            if($order_type == 0){
                $resp   = ["status" => 0, "message" => "Nama Sales belum diisi!"];
            } else {
                $resp   = ["status" => 0, "message" => "Nama Mekanik belum diisi!"];
            }
        }

        return response()->json($resp);
    }

    public function order_cancel(Request $req)
    {
        $order_id   = $req->id;

        $update    = DB::table('orders')->where("order_id", $order_id)->update(["status" => 0]);
        if($update) {
            $req->session()->flash('success', "Order berhasil dibatalkan, status menjadi draft.");
        } else {
            $req->session()->flash('error', "Order gagal dibatalkan!");
        }

        return redirect()->back();
    }

    public function generateNoInvoice(){
        $count      = DB::table("orders")->whereBetween("tgl_invoice", [date("Y-m-d")." 00:00:00", date("Y-m-d")." 23:59:59"])->get()->count();
        $count      = str_pad($count+1, 4, '0', STR_PAD_LEFT);
        $noInvoice  = date("ymd").$count;

        return $noInvoice;
    }


    public function order_list(Request $req){
        $search = $req->q;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $warehouse = $this->getWarehouse();

        $orders = DB::table('orders')->where("orders.warehouse_id" , $warehouse_id);

        if(!empty($search)){
            $orders = $orders->where("order_id", "LIKE", "%".$search."%")
                             ->orWhere("customer_name", "LIKE", "%".$search."%")
                             ->orWhere("customer_nohp", "LIKE", "%".$search."%")
                             ->orWhere("kendaraan_nopol", "LIKE", "%".$search."%")
                             ->orWhere("no_invoice", "LIKE", "%".$search."%");
        }

        $orders = $orders->orderBy("order_id", "desc")->orderBy("status", "desc")->paginate(50);

        return View::make('order_list')->with(compact("orders", "warehouse"));
    }

    public function order_payment(Request $req)
    {
        $order_id           = $req->order_id;
        $jumlah_bayar       = $req->jumlah_bayar;
        $metode_pembayaran  = $req->metode_pembayaran;

        $result = ["status" => 0, "message" => "Terjadi kesalahan saat memproses permintaan Anda. Mohon coba lagi!"];

        $order    = DB::table("orders")->where("order_id", $order_id)->first();

        if($jumlah_bayar < $order->total_harga){
            $result = ["status" => 0, "message" => "Jumlah yang harus dibayarkan adalah <b>Rp ".number_format($order->total_harga, 0, ',', '.')];
        } else {
            $kembalian = $jumlah_bayar-$order->total_harga;
            $data = [
                "pembayaran"        => $jumlah_bayar,
                "kembalian"         => $kembalian,
                "metode_pembayaran" => $metode_pembayaran,
                "tgl_invoice"       => date("Y-m-d H:i:s"),
                "no_invoice"        => $this->generateNoInvoice(),
                "status"            => 3
            ];

            $update = DB::table('orders')->where("order_id", $order_id)->update($data);

            $result = ["status" => 1, "message" => "Rp ".number_format($kembalian, 0, ',', '.')];
        }

        return response()->json($result);
    }

    public function cetak_invoice(Request $req)
    {
        $order_id       = $req->id;
        $type           = $req->type;

        $order          = DB::table("orders")
                            ->leftJoin("warehouse", "warehouse.warehouse_id", "=", "orders.warehouse_id")
                            ->where("order_id", $order_id)->first();

        $orderDetail = DB::table("orders_detail")
                    ->select("products.product_name", "products.product_code", "products.sale_price", "orders_detail.*",)
                    ->leftJoin("orders", "orders.order_id", "orders_detail.order_id")
                    ->leftJoin("products", "products.product_id", "orders_detail.product_id")
                    ->leftJoin("categories", "categories.category_id", "products.category_id")
                    ->where([["orders.order_id", $order_id], ["orders_detail.type", 0]])->orderBy("orders.order_id", "asc");

        $orderDetail = DB::table("orders_detail")
                    ->select("services.nama_jasa as product_name", "services.nama_jasa as product_code", "services.biaya as sale_price", "orders_detail.*")
                    ->leftJoin("orders", "orders.order_id", "orders_detail.order_id")
                    ->leftJoin("services", "services.service_id", "orders_detail.product_id")
                    ->where([["orders.order_id", $order_id], ["orders_detail.type", 1]])->orderBy("orders.order_id", "asc")
                    ->union($orderDetail)
                    ->get();

        $data           = [
                            "order_id"          => $order_id,
                            "no_invoice"        => $order->no_invoice,
                            "tgl_order"         => date("d/m/Y H:i:s", strtotime($order->tgl_order))." WIB",
                            "tgl_invoice"       => date("d/m/Y", strtotime($order->tgl_invoice)),
                            "total_harga"       => $order->total_harga,
                            "pembayaran"        => $order->pembayaran,
                            "kembalian"         => $order->kembalian,
                            "metode_pembayaran" => $order->metode_pembayaran,
                            "items"             => $orderDetail,
                            "diskon"            => $order->diskon,
                            "customer_name"     => $order->customer_name,
                            "customer_nohp"     => $order->customer_nohp,
                            "warehouse_address" => $order->warehouse_address,
                            "warehouse_nohp"    => $order->warehouse_nohp,
                            "order_type"        => $order->order_type,
                            "nama_mekanik"      => $order->nama_mekanik,
                            "catatan"           => $order->catatan,
                        ];

        $pdf            = PDF::loadView('download_invoice', compact("data"));
        $fn             = "invoice_".$order->no_invoice.".pdf";
        $customPaper    = array(0,0,300,400);
        PDF::setOptions(['dpi' => 203]);

        if($type == "pdf"){
            return $pdf->setPaper($customPaper)->stream($fn);
        } else {
            $pathToPdf      = public_path("temp/".$fn);
            $pdf->save($pathToPdf);
            $pdfConverter   = new PDF2Image($pathToPdf);
            $fn             = "invoice_".$order->no_invoice.".jpg";
            $pathToJpg      = public_path("temp/invoice_".$fn);
            $pdfConverter->save($pathToJpg);
            return response()->download($pathToJpg, $fn)->deleteFileAfterSend(true);
        }
    }

    public function laporan(Request $req){
        $type               = $req->reports;
        $metode_pembayaran  = $req->metode_pembayaran;
        $dl                 = $req->format;
        $startDate          = $req->startDate;
        $endDate            = $req->endDate;

        if(Session::has('selected_warehouse_id')){
            $warehouse_id = Session::get('selected_warehouse_id');
        } else {
            $warehouse_id = DB::table('warehouse')->first()->warehouse_id;
        }

        $warehouse = $this->getWarehouse();

        $total_parts    = DB::table("orders")
                        ->leftJoin("orders_detail", "orders_detail.order_id", "=", "orders.order_id")
                        ->where("orders_detail.type", 0)
                        ->where("orders.status" , 3)
                        ->where("orders.warehouse_id" , $warehouse_id)
                        ->when($req->filled('metode_pembayaran'), fn($q) => $q->where('metode_pembayaran', $req->metode_pembayaran));
        $total_jasa     = DB::table("orders")
                        ->leftJoin("orders_detail", "orders_detail.order_id", "=", "orders.order_id")
                        ->where("orders_detail.type", 1)
                        ->where("orders.status" , 3)
                        ->where("orders.warehouse_id" , $warehouse_id)
                        ->when($req->filled('metode_pembayaran'), fn($q) => $q->where('metode_pembayaran', $req->metode_pembayaran));
        if($metode_pembayaran != null){
            $orders         = DB::table('orders')->where([["orders.warehouse_id" , $warehouse_id], ["metode_pembayaran", $metode_pembayaran]]);
        } else {
            $orders         = DB::table('orders')->where("orders.warehouse_id" , $warehouse_id);
        }


        if(!empty($startDate) && !empty($endDate)){
            $total_parts    = $total_parts->whereBetween("orders.tgl_invoice", [$startDate." 00:00:00", $endDate." 23:59:59"]);
            $total_jasa     = $total_jasa->whereBetween("orders.tgl_invoice", [$startDate." 00:00:00", $endDate." 23:59:59"]);
            $orders         = $orders->whereBetween("orders.tgl_invoice", [$startDate." 00:00:00", $endDate." 23:59:59"]);
        } else {
            if($type == "yearly"){
                $total_parts    = $total_parts->whereBetween("orders.tgl_invoice", [date("Y")."-01-01 00:00:00", date("Y")."-12-31 23:59:59"]);
                $total_jasa     = $total_jasa->whereBetween("orders.tgl_invoice", [date("Y")."-01-01 00:00:00", date("Y")."-12-31 23:59:59"]);
                $orders         = $orders->whereBetween("orders.tgl_invoice", [date("Y")."-01-01 00:00:00", date("Y")."-12-31 23:59:59"]);
            } else if($type == "monthly"){
                $total_parts    = $total_parts->whereBetween("orders.tgl_invoice", [date("Y-m-d", strtotime("-30 days"))." 00:00:00", date("Y-m-d")." 23:59:59"]);
                $total_jasa     = $total_jasa->whereBetween("orders.tgl_invoice", [date("Y-m-d", strtotime("-30 days"))." 00:00:00", date("Y-m-d")." 23:59:59"]);
                $orders         = $orders->whereBetween("orders.tgl_invoice", [date("Y-m-d", strtotime("-30 days"))." 00:00:00", date("Y-m-d")." 23:59:59"]);
            } else {
                $total_parts    = $total_parts->whereBetween("orders.tgl_invoice", [date("Y-m-d")." 00:00:00", date("Y-m-d")." 23:59:59"]);
                $total_jasa     = $total_jasa->whereBetween("orders.tgl_invoice", [date("Y-m-d")." 00:00:00", date("Y-m-d")." 23:59:59"]);
                $orders         = $orders->whereBetween("orders.tgl_invoice", [date("Y-m-d")." 00:00:00", date("Y-m-d")." 23:59:59"]);
            }
        }

        // dd($orders->sum('diskon'));
        $total_qty_parts    = $total_parts->get()->count();
        $total_biaya_parts  = $total_parts->sum(DB::raw('orders_detail.price * orders_detail.amount')) - $orders->sum('diskon');
        $total_parts        = $total_parts->sum("orders_detail.amount");


        $total_qty_jasa     = $total_jasa->get()->count();
        $total_biaya_jasa   = $total_jasa->sum(DB::raw('orders_detail.price * orders_detail.amount'));
        $total_jasa         = $total_jasa->sum("orders_detail.amount");

        $total_omset        = $total_biaya_parts+$total_biaya_jasa;
        $unit_entry         = $orders->get()->count();

        $orders_tmp         = $orders->get();
        if($req->filled('format')){
            $orders             = $orders->orderBy("orders.order_id", "desc")->get();
        }else{
            $orders             = $orders->orderBy("orders.order_id", "desc")->paginate(20);
        }

        $laporan = [
            "unit_entry"        => number_format($unit_entry, 0, ",", "."),
            "total_biaya_parts" => number_format($total_biaya_parts, 0, ",", "."),
            "total_biaya_jasa"  => number_format($total_biaya_jasa, 0, ",", "."),
            "total_omset"       => number_format($total_omset, 0, ",", "."),
        ];

        $LaporanExport  = [];

        $i = 1;
        foreach($orders as $o){
            $jenis_order        = [0 => "Hanya Beli Parts", 1 => "Servis & Penggantian Parts"];
            $metodePembayaran   = [0 => "Tunai", 1 => "QRIS", 2 => "Transfer Bank", 3 => "EDC"];


            $LaporanExport[] = [
                "NO."               => $i,
                "ORDER ID"          => $o->order_id,
                "NO. INVOICE"       => $o->no_invoice,
                "TGL. INVOICE"      => date("d/m/Y", strtotime($o->tgl_invoice)),
                "JENIS ORDER"       => $jenis_order[$o->order_type],
                "NAMA CUSTOMER"     => $o->customer_name,
                "NO. HP"            => $o->customer_nohp,
                "NO. POLISI"        => $o->kendaraan_nopol,
                "JENIS KENDARAAN"   => $o->kendaraan_jenis,
                "KM KENDARAAN"      => $o->kendaraan_km,
                "TAHUN KENDARAAN"   => $o->kendaraan_tahun,
                "METODE PEMBAYARAN" => $metodePembayaran[$o->metode_pembayaran ?? 0],
                "TOTAL PEMBELIAN"   => $o->total_harga2,
                "TOTAL HARGA JUAL"  => $o->total_harga,
                "TOTAL KEUNTUNGAN"  => $o->total_harga-$o->total_harga2,
            ];

            $i++;
        }


        $grandTotalPembelian    = 0;
        foreach($orders as $o){
            $grandTotalPembelian += $o->total_harga;
        }

        $fn = 'reports_'.time();
        if($dl == "xls"){
            if(!empty($LaporanExport)){
                $heading = array_keys($LaporanExport[0]);
                return (new LaporanExport($heading, $LaporanExport))->download($fn.'.xls', \Maatwebsite\Excel\Excel::XLS);
            } else {
                $req->session()->flash('error', "Laporan belum tersedia!");
            }
        } else if($dl == "pdf"){
            if(!empty($LaporanExport)){
                $heading = array_keys($LaporanExport[0]);
                $pdf            = PDF::loadView('download_laporan', compact("heading","LaporanExport"));
                $fn             = $fn.".pdf";

                return $pdf->setPaper('A4', 'landscape')->stream($fn);
            } else {
                $req->session()->flash('error', "Laporan belum tersedia!");
            }
        }

        return View::make('laporan')->with(compact("laporan", "orders", "grandTotalPembelian", "warehouse", "total_qty_parts", "total_qty_jasa"));
    }
}
