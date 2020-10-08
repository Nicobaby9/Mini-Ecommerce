<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, Category, Customer};

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

    public function categoryProduct($slug) {
    	$products = Category::where('slug', $slug)->first()->product()->orderBy('created_at', 'DESC')->paginate(12);
    	// $categories = Category::with(['child'])->withCount(['child'])->getParent()->orderBy('name', 'ASC')->get();
    	$categories = Category::with(['child', 'parent'])->withCount(['child'])->orderBy('name', 'ASC')->get();

    	return view('layouts.ecommerce.product', compact('products', 'categories'));
    }

    public function show($slug) {
    	$product = Product::with(['category'])->where('slug', $slug)->firstOrFail();
    	$category = Category::where('id', $product->category_id)->first();

    	return view('layouts.ecommerce.show', compact('product', 'category'));
    }

    public function verifyCustomerRegistration($token) {
        $customer = Customer::where('activate_token', $token)->first();
        if($customer) {
            $customer->update([
                'activate_token' => null,
                'status' => 1
            ]);

            return redirect(route('customer.login'))->with(['success' => 'Verifikasi Berhasil, Silahkan Login']);
        }

        return redirect(route('customer.login'))->with(['error' => 'Invalid Verifikasi Token']);
    }
}
