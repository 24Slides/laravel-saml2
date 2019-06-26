<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Helpers\ConsoleHelper;
use Slides\Saml2\Repositories\TenantRepository;

/**
 * Class UpdateTenant
 *
 * @package Slides\Saml2\Commands
 */
class UpdateTenant extends \Illuminate\Console\Command
{
    use RendersTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:update-tenant {id}
                            { --k|key= : A tenant custom key }
                            { --entityId= : IdP Issuer URL }
                            { --loginUrl= : IdP Sign on URL }
                            { --logoutUrl= : IdP Logout URL }
                            { --x509cert= : x509 certificate (base64) }
                            { --metadata= : A custom metadata }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a Tenant entity (relying identity provider)';

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

        $tenant->update(array_filter([
            'key' => $this->option('key'),
            'idp_entity_id' => $this->option('entityId'),
            'idp_login_url' => $this->option('loginUrl'),
            'idp_logout_url' => $this->option('logoutUrl'),
            'idp_x509_cert' => $this->option('x509cert'),
            'metadata' => ConsoleHelper::stringToArray($this->option('metadata'))
        ]));

        if(!$tenant->save()) {
            $this->error('Tenant cannot be saved.');
            return;
        }

        $this->info("The tenant #{$tenant->id} ({$tenant->uuid}) was successfully updated.");

        $this->renderTenantCredentials($tenant);

        $this->output->newLine();
    }
}