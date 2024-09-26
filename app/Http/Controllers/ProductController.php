<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * index
     * 
     * @return View
     */
    public function index(): View
    {
        // Mendapatkan semua produk dengan pagination
        $product = new Product;
        $products = $product->get_product()
                            ->latest()
                            ->paginate(10);

        // Render view dengan produk
        return view('products.index', compact('products'));
    }

    /**
     * create
     * 
     * @return View
     */
    public function create(): View
    {
        // Membuat instansi baru dari model Product dan Supplier
        $product = new Product;
        $supplier = new Supplier;

        // Mengambil data kategori produk dan supplier
        $data['categories'] = $product->get_category_product()->get();
        $data['suppliers_'] = $supplier->get_suppliers()->get();

        return view('products.create', compact('data'));
    }

    /**
     * store
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi form
        $validateData = $request->validate([
            'image'                 => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'                 => 'required|min:5',
            'product_category_id'   => 'required|integer',
            'suppliers_id'          => 'required|integer',
            'description'           => 'required|min:10',
            'price'                 => 'required|numeric',
            'stock'                 => 'required|numeric',
        ]);

        // var_dump($validateData);exit;

        // Memeriksa apakah ada file gambar yang diunggah
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->store('public/images');
// var_dump($request);exit;
            // Menyimpan produk ke database
            Product::create([
                'image'                 => $image->hashName(),
                'title'                 => $request->title,
                'product_category_id'   => $request->product_category_id,
                'suppliers_id'          => $request->suppliers_id,  // Pastikan ini sesuai dengan field supplier_id
                'description'           => $request->description,   // Perbaiki kesalahan penulisan
                'price'                 => $request->price,
                'stock'                 => $request->stock,
            ]);

            // Redirect ke halaman index dengan pesan sukses
            return redirect()->route('products.index')->with(['success' => 'Data Telah Disimpan']);
        }

        // Redirect ke halaman index dengan pesan error
        return redirect()->route('products.index')->with(['error' => 'Data gagal Disimpan']);
}
}