<?php
namespace App\Jobs;

use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Exceptions\InternalException;


class RefundInstallmentOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
    
    public function handle()
    {
        
        if ($this->order->payment_method !== 'installment'
            || !$this->order->paid_at
            || $this->order->refund_status !== Order::REFUND_STATUS_PROCESSING) {
            return;
        }
        
        if (!$installment = Installment::query()->where('order_id', $this->order->id)->first()) {
            return;
        }
        
        foreach ($installment->items as $item) {
            
            if (!$item->paid_at || in_array($item->refund_status, [
                    InstallmentItem::REFUND_STATUS_SUCCESS,
                    InstallmentItem::REFUND_STATUS_PROCESSING,
                ])) {
                continue;
            }
            
            try {
                $this->refundInstallmentItem($item);
            } catch (\Exception $e) {
                \Log::warning('Instalment refund failed:'.$e->getMessage(), [
                    'installment_item_id' => $item->id,
                ]);
                
                continue;
            }
        }
        $installment->refreshRefundStatus();
    }
    
    protected function refundInstallmentItem(InstallmentItem $item)
    {
        
        $refundNo = $this->order->refund_no.'_'.$item->sequence;
        
        switch ($item->payment_method) {
            case 'wechat':
                app('wechat_pay')->refund([
                    'transaction_id' => $item->payment_no, 
                    'total_fee'      => $item->total * 100, 
                    'refund_fee'     => $item->base * 100, 
                    'out_refund_no'  => $refundNo, 
                    
                    'notify_url'     => ngrok_url('installments.wechat.refund_notify'),
                ]);
                
                $item->update([
                    'refund_status' => InstallmentItem::REFUND_STATUS_PROCESSING,
                ]);
                break;
            case 'alipay':
                $ret = app('alipay')->refund([
                    'trade_no'       => $item->payment_no, 
                    'refund_amount'  => $item->base, 
                    'out_request_no' => $refundNo, 
                ]);
                
                if ($ret->sub_code) {
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_FAILED,
                    ]);
                } else {
                    
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                
                throw new InternalException('Unknown order payment method:'.$item->payment_method);
                break;
        }
    }
}