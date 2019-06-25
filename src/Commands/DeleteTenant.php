<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Repositories\TenantRepository;

/**
 * Class DeleteTenant
 *
 * @package Slides\Saml2\Commands
 */
class DeleteTenant extends \Illuminate\Console\Command
{
    use RendersTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:delete-tenant {tenant}
                            { --safe : Safe deletion }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a tenant by ID, key or UUID';

    /**
     * @var TenantRepository
     */
    protected $tenants;

    /**
     * DeleteTenant constructor.
     *
     * @param TenantRepository $tenants
     */
    public function __construct(TenantRepository $tenants)
    {
        $this->tenants = $tenants;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $tenants = $this->tenants->findByAnyIdentifier($this->argument('tenant'), false);

        if($tenants->isEmpty()) {
            $this->error('Cannot find a matching tenant by "' . $this->argument('tenant') . '" identifier');
            return;
        }

        $this->renderTenants($tenants, 'Found tenant(s)');

        if($tenants->count() > 1) {
            $deletingId = $this->ask('We have found several tenants, which one would you like to delete? (enter its ID)');
        }
        else {
            $deletingId = $tenants->first()->id;
        }

        $tenant = $tenants->firstWhere('id', $deletingId);

        if($this->option('safe')) {
            $tenant->delete();

            $this->info('The tenant #' . $deletingId . ' safely deleted. To restore it, run:');
            $this->output->block('php artisan saml2:restore-tenant ' . $deletingId);

            return;
        }

        if(!$this->confirm('Would you like to forcely delete the tenant #' . $deletingId . '? It cannot be reverted.')) {
            return;
        }

        $tenant->forceDelete();

        $this->info('The tenant #' . $deletingId . ' safely deleted.');
    }
}