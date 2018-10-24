<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Twig_Environment::class, function($app) {
            $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../resources/mails');
            $twig = new \Twig_Environment($loader, array(
                'cache' => __DIR__.'/../../storage/twig/',
            ));
            return $twig;
        });
    }
}
