<?php

namespace report\reportbuild;

use Illuminate\Support\ServiceProvider;

class ReportBuildServiceProvider extends ServiceProvider
{
        public function boot() 
        {
                $this->loadViewsFrom(__DIR__.'/resources/views', 'reportbuild');
        }

        public function register()
        {
                
        }

        public function teste() {
                return '<h1>Hello, deu certo!</h1>';
        }
}