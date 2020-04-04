<?php
namespace App\Http\Requests;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Validation\Rule;

class SeckillOrderRequest extends Request
{
    public function rules()
    {
        return [
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)
            ],
            'sku_id'     => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        return $fail('This product does not exist');
                    }
                    if ($sku->product->type !== Product::TYPE_SECKILL) {
                        return $fail('This product does not support spike');
                    }
                    if ($sku->product->seckill->is_before_start) {
                        return $fail('Spike has not started');
                    }
                    if ($sku->product->seckill->is_after_end) {
                        return $fail('The spike is over');
                    }
                    if (!$sku->product->on_sale) {
                        return $fail('This product is not available');
                    }
                    if ($sku->stock < 1) {
                        return $fail('This product is sold out');
                    }
                    
                    if ($order = Order::query()
                        
                        ->where('user_id', $this->user()->id)
                        ->whereHas('items', function ($query) use ($value) {
                            
                            $query->where('product_sku_id', $value);
                        })
                        ->where(function ($query) {
                            
                            $query->whereNotNull('paid_at')
                                
                                ->orWhere('closed', false);
                        })
                        ->first()) {
                        if ($order->paid_at) {
                            return $fail('You have snapped up the product');
                        }
                        
                        return $fail('You have already placed the order, please pay at the order page');
                    }
                },
            ],
        ];
    }
}