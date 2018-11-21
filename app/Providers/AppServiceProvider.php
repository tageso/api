<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Twig_Environment::class, function ($app) {
            $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../resources/mails');
            $twig = new \Twig_Environment($loader, array(
                'cache' => __DIR__.'/../../storage/twig/',
            ));
            return $twig;
        });

        $this->app->singleton(\Aws\S3\S3Client::class, function ($app) {
            $s3 = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1',
                'endpoint' => getenv("S3_Endpoint"),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => getenv("S3_Key"),
                    'secret' => getenv("S3_Secret"),
                ],
            ]);
            return $s3;
        });
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
