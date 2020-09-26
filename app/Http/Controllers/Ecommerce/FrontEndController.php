<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, Category};

class FrontEndController extends Controller
{
    public function index() {
    	$product = Product::orderBy('created_at', 'DESC')->paginate(10);

    	return view('layouts.ecommerce.index', compact('product'));
    }

    public function product() {
    	$products = Product::orderBy('created_at', 'DESC')->paginate(12);
    	$categories = Category::with(['child'])->withCount(['child'])->getParent()->orderBy('name', 'ASC')->get();

    	return view('layouts.ecommerce.product', compact('products', 'categories'));
    }
}
