<?php namespace GeneaLabs\LaravelMultiTenantManager\Console\Commands;

use GeneaLabs\LaravelMultiTenantManager\Providers\Tool;
use Illuminate\Console\Command;

class Publish extends Command
{
    protected $signature = 'tenant:publish {--config}';
    protected $description = 'Publish various assets of the Nova Multi-Tenant Manager package.';

    public function handle()
    {
        if ($this->option('config')) {
            $this->call('vendor:publish', [
                '--provider' => Tool::class,
                '--tag' => ['config'],
                '--force' => true,
            ]);
        }
    }
}
