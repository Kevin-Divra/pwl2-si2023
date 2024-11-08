<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;




class TransactionController extends Controller
{
    /* 
    * index
    * @return void
    */

    public function index() : View
    {
        $transaction = new Transaction;
        $transactions = $transaction->get_transaction()
                            ->latest()
                            ->paginate(10);
    
        return view('transactions.index', compact('transactions'));    
    }
    
    /* 
    * create
    *
    * @return View
    */

    public function create() : View
    {
        $transaction = new Transaction;
        $product = new Product;

        $data['cashiers'] = $transaction->get_cashier()->get();
        $data['products'] = $product->get_product()->get();

        return view('transactions.create', compact('data'));    
    }

    /**
     * Store a new transaction and reduce product stock.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the request
        $request->validate([
            'nama_kasir_id' => 'required|exists:kasir,id',
            'id_product' => 'required|array|min:1',
            'id_product.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'email_pembeli' => 'required|email',
        ]);

        

        DB::beginTransaction(); // Start a transaction to ensure all operations succeed or fail together
        try {
            // Create the transaction in the transaksi_penjualan table
            $transaction = new Transaction();
            $transaction->id_kasir = $request->nama_kasir_id;
            $transaction->email_pembeli = $request->email_pembeli; 
            $transaction->save();
        
            // dd($transaction);

            // Loop through each product and its quantity
            foreach ($request->id_product as $index => $productId) {
                $product = Product::findOrFail($productId); 

                // Check if stock is sufficient
                if ($product->stock < $request->quantity[$index]) {
                    return redirect()->back()->withErrors(['error' => 'Stock for ' . $product->title . ' is insufficient.']);
                }

                // Update product stock
                $product->stock -= $request->quantity[$index];
                $product->save();

                // Insert into detail_transaksi_penjualan
                DB::table('detail_transaksi_penjualan')->insert([
                    'id_transaksi_penjualan' => $transaction->id,
                    'id_product' => $productId,
                    'jumlah_pembelian' => $request->quantity[$index],
                ]);
            }

            DB::commit(); // Commit the transaction

            // Kirim email setelah transaksi berhasil
            $this->sendEmail($request->email_pembeli, $transaction->id);

            return redirect()->route('transaction.index')->with(['success' => 'Transaction successfully created and stock updated!']);
            
            
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return redirect()->back()->withErrors(['error' => 'Failed to create transaction.']);
        }
    }

    /**
     * show
     * 
     * @param mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        // Fetch the transaction by ID
        $transaction = new Transaction;
        $data = $transaction->get_transaction()->where('transaksi_penjualan.id', $id)->firstOrFail();
    
        return view('transactions.show', compact('data'));
    }
    
    /**
     * edit
     * 
     * @param mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $transaction = new Transaction;
        $product = new Product;
        $data['transactions'] = $transaction->get_transaction()->where('transaksi_penjualan.id', $id)->firstOrFail();
        $data['cashiers'] = $transaction->get_cashier()->get();
        $data['products'] = $product->get_product()->get();

        return view('transactions.edit', compact('data'));
    }

    
    /**
     * Update
     * 
     * @param  Request $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    /**
 * Update
 * 
 * @param  Request $request
 * @param  mixed $id
 * @return RedirectResponse
 */
public function update(Request $request, $id): RedirectResponse
{
    // Validate the form input
    $request->validate([
        'nama_kasir_id' => 'required|exists:kasir,id',
        'id_product' => 'required|array|min:1',
        'id_product.*' => 'required|exists:products,id',
        'quantity' => 'required|array|min:1',
        'quantity.*' => 'required|integer|min:1',
        'email_pembeli' => 'required|email',
    ]);

    DB::beginTransaction(); // Start the transaction

    try {
        // Update transaction
        DB::table('transaksi_penjualan')->where('id', $id)->update([
            'id_kasir' => $request->input('nama_kasir_id'),
            'email_pembeli' => $request->input('email_pembeli'), // Update email
            'updated_at' => now(),
        ]);

        // Delete existing transaction details
        DB::table('detail_transaksi_penjualan')->where('id_transaksi_penjualan', $id)->delete();

        $products = $request->input('id_product');
        $quantities = $request->input('quantity');

        foreach ($products as $index => $product_id) {
            DB::table('detail_transaksi_penjualan')->insert([
                'id_transaksi_penjualan' => $id,
                'id_product' => $product_id,
                'jumlah_pembelian' => $quantities[$index],
            ]);
        }

        DB::commit(); 

        // Send email after updating transaction
        $this->sendEmail($request->input('email_pembeli'), $id);

        return redirect()->route('transaction.index')->with(['success' => 'Data Berhasil Disimpan!']);
    } catch (\Exception $e) {
        DB::rollback(); 
        Log::error($e->getMessage());

        return redirect()->route('transaction.index')->with(['error' => 'Failed to save data.']);
    }
    }

        
    /**
    * destroy
    * 
    * @param mixed $id
    * @return RedirectResponse
    */

    public function destroy($id): RedirectResponse
    {
        $transaction = Transaction::findOrFail($id);
        DB::table('detail_transaksi_penjualan')->where('id_transaksi_penjualan', $transaction->id)->delete();
        $transaction->delete();

        return redirect()->route('transaction.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

        
    private function sendEmail($to, $id){
    try {
        $transaction = new Transaction;
        $data = $transaction->get_transaction()->where('transaksi_penjualan.id', $id)->firstOrFail();

        $total_harga['transaksi'] = $data->total_transaction;
        
        $transaksi = [
            'data' => $data,
            'total_harga' => $total_harga
        ];

        Mail::send('transactions.show', $transaksi, function ($message) use ($to, $data, $total_harga) {
            $message->to($to)
                ->subject('Transaksi Details: ' . $data->email_pembeli . ' dengan Total tagihan Rp ' . number_format($total_harga['transaksi'], 2, ',', '.'));
        });

        return response()->json(['message' => 'Email sent successfully!']);
        
    } catch (\Exception $e) {
        Log::error('Failed to send email: ' . $e->getMessage());

        return response()->json(['message' => 'Failed to send email. Please try again later.'], 500);
    }
    }
}