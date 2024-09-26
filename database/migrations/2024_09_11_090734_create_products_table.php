<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->index();
            $table->foreignId('suppliers_id')->nullable()->index();
            $table->string('image');
            $table->string('title');
            $table->text('description');
            $table->bigInteger('price');
            $table->integer('stock')->default(0);
            $table->timestamps();
        });

        Schema::create('category_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_category_name');
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_addres');
            $table->string('supplier_name');
            $table->string('no_hp');
            $table->string('pic_supplier');
            $table->timestamps();
        });

        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('products_id')->nullable()->index();
            $table->foreignId('suppliers_id')->nullable()->index();
            $table->timestamps();
            $table->string('nama_kasir');
            $table->foreignId('product_category_id')->nullable()->index();
            $table->integer('jumlah_pembelian');
            $table->string('total_harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
