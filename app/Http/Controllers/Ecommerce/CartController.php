<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Mail\CustomerRegisterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{Product, Province, City, District, Customer, Order, OrderDetail};
use Mail;
use DB;

class CartController extends Controller
{
    public function getCarts() {
    	$carts = json_decode(request()->cookie('aerials-carts'), true);
    	$carts = $carts != '' ? $carts:[];

    	return $carts;
    }

    public function addToCart(Request $request) {
    	$this->validate($request, [
    		'product_id' => 'required|exists:products,id',
    		'qty' => 'required|integer'
    	]);

    	$carts = $this->getCarts();

    	if ($carts && array_key_exists($request->product_id, $carts)) {
    		$carts[$request->product_id]['qty'] += $request->qty;
    	} else {
    		$product = Product::findOrFail($request->product_id);
    		$carts[$request->product_id] = [
    			'qty' => $request->qty,
    			'product_id' => $product->id,
    			'product_name' => $product->name,
    			'product_price' => $product->price,
    			'product_image' => $product->image,
                'weight' => $product->weight
    		];
    	}

    	$cookie = cookie('aerials-carts', json_encode($carts), 2880);

    	return redirect()->back()->with(['success' => 'Product Added to Cart'])->cookie($cookie);
    }

    public function listCart() {
    	$carts = $this->getCarts();

    	$subtotal = collect($carts)->sum(function($q){
    		return $q['qty'] * $q['product_price'];
    	});

    	return view('layouts.ecommerce.cart', compact('carts', 'subtotal'));
    }

    public function updateCart(Request $request) {
    	$carts = $this->getCarts();

    	foreach ($request->product_id as $key => $row) {
    		if ($request->qty[$key] == 0) {
    			unset($carts[$row]);
    		} else {
    			$carts[$row]['qty'] = $request->qty[$key];
    		}
    	}

    	$cookie = cookie('aerials-carts', json_encode($carts), 2880);

    	return redirect()->back()->cookie($cookie);
    }

    public function checkout() {
    	$provinces = Province::orderBy('created_at', 'DESC')->get();
        $cities = City::orderBy('created_at', 'DESC')->get();
        $districts = District::orderBy('created_at', 'DESC')->get();
    	$carts = $this->getCarts();

    	$subtotal = collect($carts)->sum(function($q) {
    		return $q['qty'] * $q['product_price'];
     	});

     	return view('layouts.ecommerce.checkout', compact('provinces', 'carts', 'subtotal', 'cities', 'districts'));
    }

    public function getCity() {
    	$cities = City::where('province_id', request()->province_id)->get();

    	return response()->json(['status' => 'success', 'data' => $cities]);
    }

    public function getDistrict() {
    	$districs = District::where('city_id', request()->city_id)->get();

    	return response()->json(['status' => 'success', 'data' => $districs]);
    }

    public function checkoutProcess(Request $request) {
    	$this->validate($request, [
    		'customer_name' => 'required|string|max:100',
    		'customer_phone' => 'required',
    		'email' => 'required|email',
    		'customer_address' => 'required|string',
    		'province_id' => 'required|exists:provinces,id',
    		'city_id' => 'required|exists:cities,id',
    		'district_id' => 'required|exists:districts,id'
    	]);

    	DB::beginTransaction();

    	try{
    		//CHECK CUSTOMER DATA WITH EMAIL
    		$customer = Customer::where('email', $request->email)->first();

    		if (!auth()->guard('customer')->check() && $customer) {
    			return redirect()->back()->with(['error' => 'Silahkan Login terlebih dahulu.']);
    		}

    		$carts = $this->getCarts();
    		$subtotal = collect($carts)->sum(function($q) {
    			return $q['qty'] * $q['product_price'];
    		});

            if (!auth()->guard('customer')->check()) {
                $password = Str::random(8);
                $customer = Customer::create([
                    'name' => $request->customer_name,
                    'email' => $request->email,
                    'password' => $password,
                    'phone_number' => $request->customer_phone,
                    'address' => $request->customer_address,
                    'district_id' => $request->district_id,
                    'activate_token' => Str::random(30),
                    'status' => false
                ]);
            }

    		$order = Order::create([
    			'invoice' => Str::random(7).'-'.time(),
    			'customer_id' => $customer->id,
    			'customer_name' => $customer->name,
    			'customer_phone' => $request->customer_phone,
    			'customer_address' => $request->customer_address,
    			'district_id' => $request->district_id,
    			'subtotal' => $subtotal 
    		]);

    		foreach ($carts as $row) {
    			$product = Product::find($row['product_id']);
    			OrderDetail::create([
    				'order_id' => $order->id,
    				'product_id' => $row['product_id'],
    				'price' => $row['product_price'],
    				'qty' => $row['qty'],
    				'weight' => $product->weight
    			]);
    		}

    		DB::commit();
			$carts = [];
			$cookie = cookie('aerials-carts', json_encode($carts), 2000);

            if (!auth()->guard('customer')->check()) {
                Mail::to($request->email)->send(new CustomerRegisterMail($customer, $password));
            }
			return redirect(route('front.finish_checkout', $order->invoice))->cookie($cookie);
    	} catch (\Exception $e) {
    		DB::rollback();

    		return redirect()->back()->with(['error' => $e->getMessage()]);
    	}
    }

    public function checkoutFinish($invoice) {
        $order = Order::with(['district.city'])->where('invoice', $invoice)->first();

        return view('layouts.ecommerce.checkout_finish', compact('order'));
    }
}
