<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Repositories\TenantRepository;

/**
 * Class ListTenants
 *
 * @package Slides\Saml2\Commands
 */
class ListTenants extends \Illuminate\Console\Command
{
    use RendersTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:list-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all the tenants';

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
        $tenants = $this->tenants->all();

        if($tenants->isEmpty()) {
            $this->info('No tenants found');
            return;
        }

        $this->renderTenants($tenants);
    }
}