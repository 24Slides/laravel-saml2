<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Repositories\TenantRepository;

/**
 * Class RestoreTenant
 *
 * @package Slides\Saml2\Commands
 */
class RestoreTenant extends \Illuminate\Console\Command
{
    use RendersTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:restore-tenant {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a tenant by ID';

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
        if(!$tenant = $this->tenants->findById($this->argument('id'))) {
            $this->error('Cannot find a tenant #' . $this->argument('id'));
            return;
        }

        $this->renderTenants($tenant, 'Found a deleted tenant');

        if(!$this->confirm('Would you like to restore it?')) {
            return;
        }

        $tenant->restore();

        $this->info('The tenant #' . $tenant->id . ' successfully restored.');
    }
}