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

Route::get('/', 'Ecommerce\FrontEndController@index')->name('front.index');
Route::get('/product', 'Ecommerce\FrontEndController@product')->name('front.product');