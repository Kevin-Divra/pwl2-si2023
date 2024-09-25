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


        $transaksi = new TransaksiPenjualan();
        $transaksi_penjualan = $transaksi->get_transaksi()
                            ->latest()
                            ->paginate(10);

        return view('transaksi_penjualan.index', compact('transaksi_penjualan'));
    }
}

