<?php

namespace App\Http\Requests;

use App\Models\ProductSku;

class AddCartRequest extends Request
{
    public function rules()
    {
        return [
            'sku_id' => ['required', function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        $fail('This product does not exist');
                        return;
                    }
                    if (!$sku->product->on_sale) {
                        $fail('This product is not available');
                        return;
                    }
                    if ($sku->stock === 0) {
                        $fail('This product is sold out');
                        return;
                    }
                    if ($this->input('amount') > 0 && $sku->stock < $this->input('amount')) {
                        $fail('The product is out of stock');
                        return;
                    }
                },
            ],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }
    
    public function attributes()
    {
        return [
            'amount' => 'amount of goods'
        ];
    }
    
    public function messages()
    {
        return [
            'sku_id.required' => 'Please select a product'
        ];
    }
}
