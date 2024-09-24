<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public function get_product() {
        // get all products
        $sql = $this->select("products.*", "category_products.product_category_name as product_category_name", "suppliers.supplier_name as supplier_name")
                    ->join('category_products', 'category_products.id', '=', 'products.product_category_id') // Join tables
                    ->join('suppliers', 'suppliers.id', '=', 'products.supplier_id'); // Join tables
        return $sql;
    }    
}
