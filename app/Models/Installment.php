<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_REPAYING = 'repaying';
    const STATUS_FINISHED = 'finished';
    
    public static $statusMap = [
        self::STATUS_PENDING  => 'PENDING',
        self::STATUS_REPAYING => 'REPAYING',
        self::STATUS_FINISHED => 'FINISHED',
    ];
    
    protected $fillable = ['no', 'total_amount', 'count', 'fee_rate', 'fine_rate', 'status'];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            
            if (!$model->no) {
                
                $model->no = static::findAvailableNo();
                
                if (!$model->no) {
                    return false;
                }
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function items()
    {
        return $this->hasMany(InstallmentItem::class);
    }
    
    public static function findAvailableNo()
    {
        
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            
            $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning(sprintf('find installment no failed'));
        
        return false;
    }
    
    public function refreshRefundStatus()
    {
        $allSuccess = true;
        
        $this->load(['items']);
        foreach ($this->items as $item) {
            if ($item->paid_at && $item->refund_status !== InstallmentItem::REFUND_STATUS_SUCCESS) {
                $allSuccess = false;
                break;
            }
        }
        if ($allSuccess) {
            $this->order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        }
    }
}
