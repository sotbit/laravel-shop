<?php
namespace App\Console\Commands\Elasticsearch;

use App\Models\Product;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    
    protected $signature = 'es:sync-products {--index=products}';
    
    protected $description = 'Search';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        
        $es = app('es');
        
        Product::query()
            
            ->with(['skus', 'properties'])
            
            ->chunkById(100, function ($products) use ($es) {
                $this->info(sprintf('Syncing items with ID range %s to %s', $products->first()->id, $products->last()->id));
                
                
                $req = ['body' => []];
                
                foreach ($products as $product) {
                    
                    $data = $product->toESArray();
    
                    $req['body'][] = [
                        'index' => [
                            
                            '_index' => $this->option('index'),
                            '_type'  => '_doc',
                            '_id'    => $data['id'],
                        ],
                    ];
                    $req['body'][] = $data;
                }
                try {
                    
                    $es->bulk($req);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            });
        $this->info('Synchronization complete');
    }
}