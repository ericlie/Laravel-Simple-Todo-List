<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Form;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Form::cInput('no_telp', null, ['class' => 'form-control']); // No Telp
        Form::component('cInput', 'layouts.customInput', ['name', 'value', 'attributes', 'label']);
        Form::component('cFile', 'layouts.customFile', ['name', 'value', 'attributes', 'label']);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
