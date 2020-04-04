<?php
namespace App\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

class Migrate extends Command
{
    protected $signature = 'es:migrate';
    protected $description = 'Search';
    protected $es;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $this->es = app('es');
        
        $indices = [Indices\ProjectIndex::class];
        
        foreach ($indices as $indexClass) {
            
            $aliasName = $indexClass::getAliasName();
            $this->info('Processing index '.$aliasName);
            
            if (!$this->es->indices()->exists(['index' => $aliasName])) {
                $this->info('Index does not exist, ready to create');
                $this->createIndex($aliasName, $indexClass);
                $this->info('Successfully created, ready to initialize data');
                $indexClass::rebuild($aliasName);
                $this->info('Successful operation');
                continue;
            }
            
            try {
                $this->info('Index exists, ready to update');
                $this->updateIndex($aliasName, $indexClass);
            } catch (\Exception $e) {
                $this->warn('Update failed, ready to rebuild');
                $this->reCreateIndex($aliasName, $indexClass);
            }
            $this->info($aliasName.' Successful operation');
        }
    }
    
    protected function createIndex($aliasName, $indexClass)
    {
        
        $this->es->indices()->create([
            
            'index' => $aliasName.'_0',
            'body'  => [
                
                'settings' => $indexClass::getSettings(),
                'mappings' => [
                    '_doc' => [
                        
                        'properties' => $indexClass::getProperties(),
                    ],
                ],
                'aliases'  => [
                    
                    $aliasName => new \stdClass(),
                ],
            ],
        ]);
    }
    
    protected function updateIndex($aliasName, $indexClass)
    {
        
        $this->es->indices()->close(['index' => $aliasName]);
        
        $this->es->indices()->putSettings([
            'index' => $aliasName,
            'body'  => $indexClass::getSettings(),
        ]);
        
        $this->es->indices()->putMapping([
            'index' => $aliasName,
            'type'  => '_doc',
            'body'  => [
                '_doc' => [
                    'properties' => $indexClass::getProperties(),
                ],
            ],
        ]);
        
        $this->es->indices()->open(['index' => $aliasName]);
    }
    
    
    protected function reCreateIndex($aliasName, $indexClass)
    {
        
        $indexInfo     = $this->es->indices()->getAliases(['index' => $aliasName]);
        
        $indexName = array_keys($indexInfo)[0];
        
        if (!preg_match('~_(\d+)$~', $indexName, $m)) {
            $msg = 'Index name is incorrect:'.$indexName;
            $this->error($msg);
            throw new \Exception($msg);
        }
        
        $newIndexName = $aliasName.'_'.($m[1] + 1);
        $this->info('Creating index'.$newIndexName);
        $this->es->indices()->create([
            'index' => $newIndexName,
            'body'  => [
                'settings' => $indexClass::getSettings(),
                'mappings' => [
                    '_doc' => [
                        'properties' => $indexClass::getProperties(),
                    ],
                ],
            ],
        ]);
        $this->info('Successfully created, ready to rebuild data');
        $indexClass::rebuild($newIndexName);
        $this->info('Successful reconstruction, ready to modify alias');
        $this->es->indices()->putAlias(['index' => $newIndexName, 'name' => $aliasName]);
        $this->info('Successful modification, ready to delete old index');
        $this->es->indices()->delete(['index' => $indexName]);
        $this->info('successfully deleted');
    }
}