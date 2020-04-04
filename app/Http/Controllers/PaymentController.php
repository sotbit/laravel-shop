<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use App\Events\OrderPaid;
use Illuminate\Validation\Rule;
use App\Models\Installment;

class PaymentController extends Controller
{
    
    public function payByAlipay(Order $order, Request $request)
    {
        
        $this->authorize('own', $order);
        
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('Order status is incorrect');
        }
        
        
        return app('alipay')->web([
            'out_trade_no' => $order->no, 
            'total_amount' => $order->total_amount, 
            'subject'      => 'Pay Shop Test orders：'.$order->no, 
        ]);
    }
    
    
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => 'The data is incorrect']);
        }
        return view('pages.success', ['msg' => 'Successful payment']);
    }
    
    
    public function alipayNotify()
    {
        
        $data  = app('alipay')->verify();
        
        
        if(!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }
        
        $order = Order::where('no', $data->out_trade_no)->first();
        
        if (!$order) {
            return 'fail';
        }
        
        if ($order->paid_at) {
            
            return app('alipay')->success();
        }
    
        $order->update([
            'paid_at'        => Carbon::now(), 
            'payment_method' => 'alipay', 
            'payment_no'     => $data->trade_no, 
        ]);
    
        $this->afterPaid($order); 
        return app('alipay')->success();
    }
    
    
    public function payByWechat(Order $order, Request $request) {
        
        $this->authorize('own', $order);
        
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('Order status is incorrect');
        }
        
        $wechatOrder =  app('wechat_pay')->scan([
            'out_trade_no' => $order->no,  
            'total_fee' => $order->total_amount * 100, 
            'body'      => 'Pay Shop Test orders：'.$order->no, 
        ]);
        
        
        $qrCode = new QrCode($wechatOrder->code_url);
    
        
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
    }
    
    
    public function wechatNotify()
    {
        
        $data  = app('wechat_pay')->verify();
        
        $order = Order::where('no', $data->out_trade_no)->first();
        
        if (!$order) {
            return 'fail';
        }
        
        if ($order->paid_at) {
            
            return app('wechat_pay')->success();
        }
        
        
        $order->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => 'wechat',
            'payment_no'     => $data->transaction_id,
        ]);
    
        $this->afterPaid($order); 
        return app('wechat_pay')->success();
    }
    
    
    public function wechatRefundNotify(Request $request)
    {
        
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        $data = app('wechat_pay')->verify(null, true);
        
        
        if(!$order = Order::where('no', $data['out_trade_no'])->first()) {
            return $failXml;
        }
        
        if ($data['refund_status'] === 'SUCCESS') {
            
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        } else {
            
            $extra = $order->extra;
            $extra['refund_failed_code'] = $data['refund_status'];
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
                'extra' => $extra
            ]);
        }
        
        return app('wechat_pay')->success();
    }
    
    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
    
    
    public function payByInstallment(Order $order, Request $request)
    {
        
        $this->authorize('own', $order);
        
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('Order status is incorrect');
        }
        
        $this->validate($request, [
            'count' => ['required', Rule::in(array_keys(config('app.installment_fee_rate')))],
        ]);
        
        Installment::query()
            ->where('order_id', $order->id)
            ->where('status', Installment::STATUS_PENDING)
            ->delete();
        $count = $request->input('count');
        
        $installment = new Installment([
            
            'total_amount' => $order->total_amount,
            
            'count'        => $count,
            
            'fee_rate'     => config('app.installment_fee_rate')[$count],
            
            'fine_rate'    => config('app.installment_fine_rate'),
        ]);
        $installment->user()->associate($request->user());
        $installment->order()->associate($order);
        $installment->save();
        
        $dueDate = Carbon::tomorrow();
        
        $base = big_number($order->total_amount)->divide($count)->getValue();
        
        $fee = big_number($base)->multiply($installment->fee_rate)->divide(100)->getValue();
        
        for ($i = 0; $i < $count; $i++) {
            
            if ($i === $count - 1) {
                $base = big_number($order->total_amount)->subtract(big_number($base)->multiply($count - 1));
            }
            $installment->items()->create([
                'sequence' => $i,
                'base'     => $base,
                'fee'      => $fee,
                'due_date' => $dueDate,
            ]);
            
            $dueDate = $dueDate->copy()->addDays(30);
        }
        
        return $installment;
    }
}
