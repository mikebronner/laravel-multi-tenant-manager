<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelMultiTenantManager\Console\Commands;

use GeneaLabs\LaravelMultiTenantManager\Exceptions\TenantDoesNotExistException;
use GeneaLabs\LaravelMultiTenantManager\Services\Tenant;
use Illuminate\Console\Command;

class DeleteTenant extends Command
{
    protected $signature = 'tenant:delete {domain?} {--all : Deletes all tenants.}';
    protected $description = 'Deletes a tenant by the provided domain.';

    public function handle(): void
    {
        if ($this->option("all")) {
            $this->deleteAllTenants();

            return;
        }

        $domain = $this->argument('domain');

        try {
            if ($this->confirmDeletion($domain)) {
                $this->deleteTenant($domain);
            }
        } catch (TenantDoesNotExistException $exception) {
            $this->error($exception->getMessage());
        }
    }

    protected function confirmDeletion(string $domain): bool
    {
        return "y" === $this->ask("Are you sure you want to delete tenant '{$domain}'? Type 'y' to confirm. [n]");
    }

    protected function confirmDeletionOfAllTenants(): bool
    {
        return "y" === $this->ask("Are you sure you want to delete ALL tenants? This operation is irreversible! Type 'y' to confirm. [n]");
    }

    protected function deleteAllTenants(): void
    {
        if (! $this->confirmDeletionOfAllTenants()) {
            $this->info("Delete command aborted.");

            return;
        }

        app("db")
            ->table("hostnames")
            ->get()
            ->each(function (object $hostname): void {
                $this->deleteTenant($hostname->fqdn);
                $this->dropSchema($hostname->fqdn);
                $this->deleteHostname($hostname->fqdn);
            });
        app("db")
            ->table("websites")
            ->get()
            ->each(function (object $website): void {
                $this->deleteTenant($website->uuid);
                $this->dropSchema($website->uuid);
                $this->deleteWebsite($website->uuid);
            });
    }

    protected function deleteHostname(object $fqdn): void
    {
        app("db")
            ->table("hostnames")
            ->where("fqdn", $fqdn)
            ->delete();
        $this->info("✅  Hostname '{$fqdn}' successfully deleted.");
    }

    protected function deleteTenant(string $domain): void
    {
        try {
            (new Tenant)->delete($domain);
            $this->info("✅  Tenant '{$domain}' successfully deleted.");
        } catch (TenantDoesNotExistException $exception) {
            $this->error("⛔️  Tenant '{$domain}' record doesn't exist.");
        }
    }

    protected function dropSchema(string $domain): void
    {
        if ($domain === "public") {
            return;
        }

        app("db")
            ->connection("system")
            ->statement("DROP SCHEMA IF EXISTS {$domain} CASCADE");
        $this->info("✅  Schema '{$domain}' successfully dropped.");
    }

    protected function deleteWebsite(string $uuid): void
    {
        app("db")
            ->table("websites")
            ->where("uuid", $uuid)
            ->delete();
        $this->info("✅  Website '{$uuid}' successfully deleted.");
    }
}
