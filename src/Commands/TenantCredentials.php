<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Helpers\ConsoleHelper;
use Slides\Saml2\Repositories\TenantRepository;

/**
 * Class TenantCredentials
 *
 * @package Slides\Saml2\Commands
 */
class TenantCredentials extends \Illuminate\Console\Command
{
    use RendersTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:tenant-credentials {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List tenant credentials for IdP';

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

        $this->renderTenants($tenant, 'The tenant model');
        $this->renderTenantCredentials($tenant);

        $this->output->newLine();
    }
}