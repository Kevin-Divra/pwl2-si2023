<?php

namespace App\Http\Controllers;


use App\Models\TransaksiPenjualan;

use illuminate\View\View;

use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    /**
     * index
     * 
     * @return void
     */
    public function index() : View
    {
        // //get all product
        // $products = Product::select("products:*", "category_product.product_category_name as product_category_name")
        //                      ->join('category_product', 'category_product.id', '=', 'product.product_category_id')
        //                      ->latest()
        //                      ->paginate(10);

        $transaksi = new TransaksiPenjualan();
        $transaksi_penjualan = $transaksi->get_transaksi()
                            ->latest()
                            ->paginate(10);

        return view('transaksi_penjualan.index', compact('transaksi_penjualan'));
    }
}

