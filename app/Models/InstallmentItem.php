<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Moontoast\Math\BigNumber;

class InstallmentItem extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';
    
    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => 'PENDING',
        self::REFUND_STATUS_PROCESSING => 'PROCESSING',
        self::REFUND_STATUS_SUCCESS    => 'SUCCESS',
        self::REFUND_STATUS_FAILED     => 'FAILED',
    ];
    
    protected $fillable = [
        'sequence',
        'base',
        'fee',
        'fine',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
    ];
    protected $dates = ['due_date', 'paid_at'];
    
    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }
    
    
    public function getTotalAttribute()
    {
        
        $total = big_number($this->base, 2)->add($this->fee);
        if (!is_null($this->fine)) {
            $total->add($this->fine);
        }
        return $total->getValue();
    }
    
    
    public function getIsOverdueAttribute()
    {
        return Carbon::now()->gt($this->due_date);
    }
}
