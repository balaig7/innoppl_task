<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
        'description',
        'sku',
        'images',
        'amount'
    ]; 

    public function user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
