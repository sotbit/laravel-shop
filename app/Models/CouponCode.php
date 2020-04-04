<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exceptions\CouponCodeUnavailableException;

class CouponCode extends Model
{
    
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';
    
    public static $typeMap = [
        self::TYPE_FIXED   => 'FIXED',
        self::TYPE_PERCENT => 'PERCENT',
    ];
    
    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];
    protected $casts = [
        'enabled' => 'boolean',
    ];
    
    protected $dates = ['not_before', 'not_after'];
    
    protected $appends = ['description'];
    
    public function getDescriptionAttribute()
    {
        $str = '';
        if ($this->min_amount > 0) {
            $str = 'Full '.str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str.'Discount'.str_replace('.00', '', $this->value).'%';
        }
        return $str.'Full '.str_replace('.00', '', $this->value);
    }
    
    
    public static function findAvailableCode($length = 16)
    {
        do {
            
            $code = strtoupper(Str::random($length));
            
        } while (self::query()->where('code', $code)->exists());
        
        return $code;
    }
    
    
    public function checkAvailable(User $user, $orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('Not enable');
        }
        
        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('The coupon has been redeemed');
        }
        
        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('This coupon is not yet available');
        }
        
        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('The coupon has expired');
        }
        
        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('The order amount does not meet the minimum amount of the coupon');
        }
        $used = Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNull('paid_at')
                        ->where('closed', false);
                })->orWhere(function($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                });
            })->exists();
        if ($used) {
            throw new CouponCodeUnavailableException('You have already used this coupon');
        }
    }
    
    
    public function getAdjustedPrice($orderAmount)
    {
        
        if ($this->type === self::TYPE_FIXED) {
            
            return max(0.01, $orderAmount - $this->value);
        }
        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }
    
    
    public function changeUsed($increase = true)
    {
        
        if ($increase) {
            
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
