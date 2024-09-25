<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPenjualan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_penjualan';
    public function get_transaksi() {
        // get all transactions with product and category details
        $sql = $this->select("transaksi_penjualan.*", "category_products.product_category_name", "products.title", "products.price")
                    ->join('products', 'products.id', '=', 'transaksi_penjualan.products_id') 
                    ->join('category_products', 'category_products.id', '=', 'products.product_category_id')
                    ->join('suppliers', 'suppliers.id', '=', 'transaksi_penjualan.suppliers_id'); 
        return $sql;
    }  
}
