<?php
namespace App\Console\Commands\Cron;

use App\Models\Installment;
use App\Models\InstallmentItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateInstallmentFine extends Command
{
    protected $signature = 'cron:calculate-installment-fine';
    
    protected $description = 'Calculate installment overdue fee';
    
    public function handle()
    {
        InstallmentItem::query()
            
            ->with(['installment'])
            ->whereHas('installment', function ($query) {
                
                $query->where('status', Installment::STATUS_REPAYING);
            })
            
            ->where('due_date', '<=', Carbon::now())
            
            ->whereNull('paid_at')
            
            ->chunkById(1000, function ($items) {
                
                foreach ($items as $item) {
                    
                    $overdueDays = Carbon::now()->diffInDays($item->due_date);
                    
                    $base = big_number($item->base)->add($item->fee)->getValue();
                    
                    $fine = big_number($base)
                        ->multiply($overdueDays)
                        ->multiply($item->installment->fine_rate)
                        ->divide(100)
                        ->getValue();
                    
                    
                    $fine = big_number($fine)->compareTo($base) === 1 ? $base : $fine;
                    $item->update([
                        'fine' => $fine,
                    ]);
                }
            });
    }
}