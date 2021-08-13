<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelMultiTenantManager\Console\Commands;

use GeneaLabs\LaravelMultiTenantManager\Exceptions\TenantExistsException;
use GeneaLabs\LaravelMultiTenantManager\Services\Tenant;
use Illuminate\Console\Command;

class AliasTenant extends Command
{
    protected $signature = 'tenant:alias {domain} {alias}';
    protected $description = 'Creates an alias for an existing tenant with the provided domain name.';

    public function handle()
    {
        $alias = $this->argument('alias');
        $domain = $this->argument('domain');

        try {
            (new Tenant)->createAlias($domain, $alias);
            $this->info("✅  New alias for tenant '{$domain}' created and now accessible at 'https://{$alias}'.");
        } catch (TenantExistsException $exception) {
            $this->error($exception->getMessage());
        }
    }
}
