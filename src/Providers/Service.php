<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelMultiTenantManager\Providers;

use GeneaLabs\LaravelMultiTenantManager\Console\Commands\AliasTenant;
use GeneaLabs\LaravelMultiTenantManager\Console\Commands\CreateTenant;
use GeneaLabs\LaravelMultiTenantManager\Console\Commands\DeleteTenant;
use GeneaLabs\LaravelMultiTenantManager\Console\Commands\Publish;
use GeneaLabs\LaravelMultiTenantManager\Http\Middleware\Authorize;
use GeneaLabs\LaravelMultiTenantManager\Tenant;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class Service extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'genealabs-laravel-multi-tenant-manager'
        );

        $this->app->singleton("tenant", function () {
            $website = app(Environment::class)->tenant();

            if (! $website) {
                $websiteUuid = config('database.connections.tenant.uuid');
                $website = (new Website)->where("uuid", $websiteUuid)->first();
                $environment = app(Environment::class);
                $environment->tenant($website);
            }

            return (new Tenant)
                ->where("website_id", $website->id)
                ->first();
        });

        $this->app->booted(function () {
            $this->routes();
        });
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }
    }

    public function register()
    {
        $this->commands(AliasTenant::class);
        $this->commands(CreateTenant::class);
        $this->commands(DeleteTenant::class);
    }
}
