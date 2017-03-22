<?php
namespace Msonowal\Laracart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function provides()
    {
        return ['cart'];
    }

    public function boot()
    {
        $this->publishConfiguration();
        $this->publishMigrations();
    }
    public function register()
    {
        $config = __DIR__ . '/../config/laracart.php';
        $this->mergeConfigFrom($config, 'laracart');
        $this->app->singleton('cart', Cart::class);
    }
    public function publishConfiguration()
    {
        $path   =   realpath(__DIR__.'/../config/laracart.php');
        $this->publishes([$path => config_path('laracart.php')], 'config');
    }
    public function publishMigrations()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('/migrations')
        ], 'migrations');
//        if ( ! class_exists('CreateCartTable')) {
//            $timestamp = date('Y_m_d_His', time());
//            $path   =   realpath(__DIR__.'/../database/migrations/0000_00_00_000000_create_cart_table.php');
//            $this->publishes([
//                $path => database_path('migrations/'.$timestamp.'_create_cart_table.php'),
//            ], 'migrations');
//        }
//        if ( ! class_exists('CreateCartItemsTable')) {
//            $timestamp = date('Y_m_d_His', time()+1);
//            $path   =   realpath(__DIR__.'/../database/migrations/0000_00_00_000001_create_cart_items_table.php');
//            $this->publishes([
//                $path => database_path('migrations/'.$timestamp.'_create_cart_items_table.php'),
//            ], 'migrations');
//        }
    }
}
