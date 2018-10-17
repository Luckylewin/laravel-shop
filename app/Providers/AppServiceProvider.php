<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 往服务容器中注入一个名为 alipay 的单例对象
        $this->app->singleton('alipay', function() {
            $config = config('pay.alipay');
            $config['notify_url'] = route('payment.alipay.notify');
            $config['notify_url'] = 'http://requestbin.fullcontact.com/ymf2rnym';
            $config['return_url'] = route('payment.alipay.return');
            // sandbox account : tcnvce0087@sandbox.com

           // 判断当前项目运行环境是否为线上环境
           if (app()->environment() !== 'production') {
               $config['mode']         = 'dev';
               $config['log']['level'] = Logger::DEBUG;
           } else {
               $config['log']['level'] = Logger::WARNING;
           }

           // 调用 Yansonda\Pay 来创建一个支付宝支付对象
           return Pay::alipay($config);
        });

        // 往服务容器中注入一个名为 wechat 的单例对象
        $this->app->singleton('wechat_pay', function() {
            $config = config('pay.wechat');
            $config['notify_url'] = route('payment.wechat.notify');

            if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::wechat($config);
        });

    }
}
