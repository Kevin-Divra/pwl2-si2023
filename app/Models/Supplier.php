<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    // protected $table = 'supplier';

    protected $fillable = ['supplier_addres', 'supplier_name', 'no_hp', 'pic_supplier'];

    public $timestamps = true;
}
