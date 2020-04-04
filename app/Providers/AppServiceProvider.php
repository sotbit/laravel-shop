<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder as ESClientBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
         \View::composer(['products.index', 'products.show'], \App\Http\ViewComposers\CategoryTreeComposer::class);
    
        Carbon::setLocale('zh');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
                $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');
                        $config['notify_url'] = ngrok_url('payment.alipay.notify');
            $config['return_url'] = route('payment.alipay.return');
            
                        if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
                        return Pay::alipay($config);
        });
    
        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
                        $config['notify_url'] = ngrok_url('payment.wechat.notify');
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
                        return Pay::wechat($config);
        });
    
                $this->app->singleton('es', function () {
                        $builder = ESClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));
                        if (app()->environment() === 'local') {
                                $builder->setLogger(app('log')->getMonolog());
            }
        
            return $builder->build();
        });
    }
}
