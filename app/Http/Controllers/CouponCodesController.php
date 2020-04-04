<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CouponCode;
use App\Exceptions\CouponCodeUnavailableException;

class CouponCodesController extends Controller
{
    public function show($code, Request $request)
    {
        if (!$record = CouponCode::where('code', $code)->first()) {
            throw new CouponCodeUnavailableException('Coupon does not exist');
        }
        $record->checkAvailable($request->user());
        return $record;
    }
}
