<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrowdfundingProduct extends Model
{
    
    const STATUS_FUNDING = 'funding';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';
    
    public static $statusMap = [
        self::STATUS_FUNDING => 'FUNDING',
        self::STATUS_SUCCESS => 'SUCCESS',
        self::STATUS_FAIL    => 'FAIL',
    ];
    
    protected $fillable = ['total_amount', 'target_amount', 'user_count', 'status', 'end_at'];
    
    protected $dates = ['end_at'];
    
    public $timestamps = false;
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    
    public function getPercentAttribute()
    {
        
        $value = $this->attributes['total_amount'] / $this->attributes['target_amount'];
        
        return floatval(number_format($value * 100, 2, '.', ''));
    }
}
