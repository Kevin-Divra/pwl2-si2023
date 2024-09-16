<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * index
     * 
     * @return View
     */

     public function index() : View
     {
        $suppliers = Supplier::latest()->paginate(10);

        return view('supplier.index', compact('suppliers'));
    
    }
}
