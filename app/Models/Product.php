<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    /**
     * fillable
     * 
     * @var array
     */
    protected $fillable = [
        'image',                
        'title',               
        'product_category_id',   
        'suppliers_id',  // Perbaiki nama field menjadi supplier_id
        'description',          
        'price',                
        'stock',        
    ];

    /**
     * Get all products with category and supplier
     */
    public function get_product() {
        // Get all products with category and supplier names
        $sql = $this->select("products.*", "category_products.product_category_name", "suppliers.supplier_name", )
                    ->join('category_products', 'category_products.id', '=', 'products.product_category_id') // Join tables
                    ->join('suppliers', 'suppliers.id', '=', 'products.suppliers_id'); // Perbaiki nama field menjadi supplier_id

        return $sql;
    }    

    /**
     * Get all category products
     */
    public function get_category_product() {
        return DB::table('category_products')->select('*');
    }

    public $timestamps = true;

    /**
     * Store a new product
     */
    public static function storeProduct($request, $image)
    {
        // Simpan produk baru 
        return self::create([
            'image'                 => $image->hashName(),
            'title'                 => $request->title,
            'product_category_id'   => $request->product_category_id,
            'suppliers_id'          => $request->suppliers_id,  // Sesuaikan dengan field supplier_id
            'descrption'            => $request->description,
            'price'                 => $request->price,
            'stock'                 => $request->stock
        ]);
    }
}
