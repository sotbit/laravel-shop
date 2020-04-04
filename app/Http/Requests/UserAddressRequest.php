<?php

namespace App\Http\Requests;

class UserAddressRequest extends Request
{
    public function rules()
    {
        return [
            'province'      => 'required',
            'city'          => 'required',
            'district'      => 'required',
            'address'       => 'required',
            'zip'           => 'required',
            'contact_name'  => 'required',
            'contact_phone' => 'required',
        ];
    }
    
    public function attributes()
    {
        return [
            'province'      => 'province',
            'city'          => 'city',
            'district'      => 'district',
            'address'       => 'address',
            'zip'           => 'zip',
            'contact_name'  => 'contact_name',
            'contact_phone' => 'contact_phone',
        ];
    }
}
