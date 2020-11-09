<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{Order, Payment, OrderReturn};
use Carbon\Carbon;
use DB;
use PDF;
use Gate;

class OrderController extends Controller
{
    public function index() {
    	$orders = Order::withCount(['return'])->where('customer_id', auth()->guard('customer')->user()->id)->orderBy('created_at', 'DESC')->paginate(10);
    	return view('layouts.ecommerce.orders.index', compact('orders'));
    }

    public function view($invoice) {
    	$order = Order::with(['district.city.province', 'details', 'details.product', 'payment'])->where('invoice', $invoice)->first();

        if (Order::where('invoice', $invoice)->exists()) {
            if (\Gate::forUser(auth()->guard('customer')->user())->allows('order-view', $order)) {

                return view('layouts.ecommerce.orders.view', compact('order'));     
            }
        } else {
            return redirect()->back();
        }

    	return redirect(route('customer.orders'))->with(['error' => 'Anda tidak diizinkan untuk mengakses order orang lain.']);
    }

    public function paymentForm() {
    	return view('layouts.ecommerce.payment');
    }

    public function storePayment(Request $request) {
    	$this->validate($request, [
    		'invoice' => 'required|exists:orders,invoice',
	        'name' => 'required|string',
	        'transfer_to' => 'required|string',
	        'transfer_date' => 'required',
	        'amount' => 'required|integer',
	        'proof' => 'required|image|mimes:jpg,png,jpeg'
    	]);

    	DB::beginTransaction();
    	try{ 
    		$order = Order::where('invoice', $request->invoice)->first();

    		if ($order->subtotal != $request->amount) {
    			$file = $request->file('proof');
    			$filename = time() . '.' . $file->getClientOriginalExtension();
    			$file->storeAs('public/payment', $filename);

    			Payment::create([
    				'order_id' => $order->id,
    				'name' => $request->name,
    				'transfer_to' => $request->transfer_to,
    				'transfer_date' => Carbon::parse($request->transfer_date)->format('Y-m-d'),
    				'amount' => $request->amount,
    				'proof' => $filename,
    				'status' => false
    			]);

    			$order->update(['status' => 1]);

    			DB::commit();

    			return redirect()->back()->with(['error' => 'Error, Pembayaran Harus Sama Dengan Tagihan.']);
    		}

    		return redirect()->back()->with(['error' => 'Errpr, upload bukti transfer']);
    	} catch(\Exception $e) {
    		DB::rollback();

    		return redirect()->back()->with(['error' => $e->getMessate()]);
    	}
    }

    public function pdf($invoice) {
        $order = Order::with(['district.city.province', 'details', 'details.product', 'payment'])->where('invoice', $invoice)->first();

        if (!Gate::forUser(auth()->guard('customer')->user())->allows('order-view', $order)) {
            return redirect(route('customer.view_order', $order->invoice));
        }

        $pdf = PDF::loadView('layouts.ecommerce.orders.pdf', compact('order'));

        return $pdf->stream();
    }

    public function acceptOrder(Request $request) {
        $order = Order::findOrFail($request->order_id);
        if (!Gate::forUser(auth()->guard('customer')->user())->allows('order-view', $order)) {
            return redirect()->back()->with(['error' => 'Bukan pesanan anda.']);
        }

        $order->update(['status' => 4]);
        return redirect()->back()->with(['success' => 'Pesanan Dikonfirmasi']);
    }

    public function returnForm($invoice) {
        $order = Order::where('invoice', $invoice)->first();

        return view('layouts.ecommerce.orders.return', compact('order'));
    }

    public function returnProcess(Request $request, $id) {
        $this->validate($request, [
            'reason' => 'required|string',
            'refund_transfer' => 'required|string',
            'photo' => 'required|image|mimes:jpg,png,jpeg'
        ]);

        $return = OrderReturn::where('order_id', $id)->first();

        if($return) return redirect()->back()->with(['error' => 'Permintaan Refund dalam proses.']);

        if($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().Str::random(5).'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/return', $filename);

            OrderReturn::create([
                'order_id' => $id,
                'photo' => $filename,
                'reason' => $request->reason,
                'refund_transfer' => $request->refund_transfer,
                'status' => 0
            ]);

            $order = Order::findOrFail($id);
            $this->sendMessage('##' . $order->invoice, $request->reason);

            return redirect()->back()->with(['success' => 'Permintaan refund dikirim.']);
        }
    }

    private function getTelegram($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $params);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $content = curl_exec($ch);
        curl_close($ch);

        return json_decode($content, true);
    }

    private function sendMessage($order_id, $reason) {
        $key = env('TELEGRAM_KEY');

        $chat = $this->getTelegram('https://api.telegram.org/' . $key . 'getUpdates', '');
        if ($chat['ok']) {
            $chat_id = $chat['result'][0]['message']['chat']['id'];
            $text = 'Hi Aerials, OrderID ' . $order_id . ' Melakukan Permintaan Refund Dengan Alasan "' . $reason . '", Segera Di check ya!';
            return $this->getTelegram('https://api.telegram.org' . $key . '/sendMessage' . '?chat_id=' . $chat_id . '$text=' . $text);
        }
    }
}
