<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(
	[
		'prefix' => 'administrator', 
		'middleware' => 'auth'
	],

	function () {
		Route::get('/home', 'HomeController@index')->name('home');
		Route::resource('category', 'Category\CategoryController')->except(['create', 'show']);	
		Route::resource('product', 'Product\ProductController')->except(['show']);
		Route::get('/product/uploadExcel', 'Product\ProductController@massUploadForm')->name('product.uploadExcel');
		Route::post('/product/uploadExcel', 'Product\ProductController@massUpload')->name('product.saveUploadExcel');
});

Route::namespace('Ecommerce')->group(function() {
	Route::get('/', 'FrontEndController@index')->name('front.index');
	Route::get('/product', 'FrontEndController@product')->name('front.product');
	Route::get('/category/{slug}', 'FrontEndController@categoryProduct')->name('front.category');
	Route::get('/product/{slug}', 'FrontEndController@show')->name('front.show_product');
	Route::get('/cart', 'CartController@listCart')->name('front.list_cart');
	Route::post('cart', 'CartController@addToCart')->name('front.cart');
	Route::post('/cart/update', 'CartController@updateCart')->name('front.update_cart');
	Route::get('/checkout', 'CartController@checkout')->name('front.checkout');
	Route::post('/checkout', 'CartController@checkoutProcess')->name('front.store_checkout');
	Route::get('/checkout/{invoice}', 'CartController@checkoutFinish')->name('front.finish_checkout');
});

Route::group(['prefix' => 'member', 'namespace' => 'ecommerce'], function() {
	Route::get('login', 'LoginController@loginForm')->name('customer.login');
	Route::get('verify/{token}', 'FrontEndController@verifyCustomerRegistration')->name('customer.verify');
	Route::post('login', 'LoginController@login')->name('customer.post_login');
	Route::group(['middleware' => 'customer'], function() {
		Route::get('dashboard', 'LoginController@dashboard')->name('customer.dashboard');
		Route::get('logout', 'LoginController@logout')->name('customer.logout');
		Route::get('orders', 'OrderController@index')->name('customer.orders');
		Route::get('orders/{invoice}', 'OrderController@view')->name('customer.view_order');
		Route::get('payment', 'OrderController@paymentForm')->name('customer.paymentForm');
		Route::post('payment', 'OrderController@storePayment')->name('customer.storePayment');
		Route::get('setting', 'FrontEndController@customerSettingForm')->name('customer.settingForm');
		Route::post('setting', 'FrontEndController@customerSettingForm')->name('customer.setting');
		Route::get('orders/{invoice}', 'OrderController@view')->name('customer.view_order');
		Route::get('orders/pdf/{invoice}', 'OrderController@pdf')->name('customer.order_pdf');
	});
});