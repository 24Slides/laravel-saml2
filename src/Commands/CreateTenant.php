<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Helpers\ConsoleHelper;
use Slides\Saml2\Repositories\TenantRepository;

/**
 * Class CreateTenant
 *
 * @package Slides\Saml2\Commands
 */
class CreateTenant extends \Illuminate\Console\Command
{
    use RendersTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:create-tenant
                            { --k|key= : A tenant custom key }
                            { --entityId= : IdP Issuer URL }
                            { --loginUrl= : IdP Sign on URL }
                            { --logoutUrl= : IdP Logout URL }
                            { --metadata= : A custom metadata }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Tenant entity (relying identity provider)';

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
        if (!$entityId = $this->option('entityId')) {
            $this->error('Entity ID must be passed as an option --entityId');
            return;
        }

        if (!$loginUrl = $this->option('loginUrl')) {
            $this->error('Login URL must be passed as an option --loginUrl');
            return;
        }

        if (!$logoutUrl = $this->option('logoutUrl')) {
            $this->error('Logout URL must be passed as an option --logoutUrl');
            return;
        }

        $key = $this->option('key');
        $metadata = ConsoleHelper::stringToArray($this->option('metadata'));

        if($key && ($tenant = $this->tenants->findByKey($key))) {
            $this->renderTenants($tenant, 'Already found tenant(s) using this key');
            $this->error(
                'Cannot create a tenant because the key is already being associated with other tenants.'
                    . PHP_EOL . 'Firstly, delete tenant(s) or try to create with another with another key.'
            );

            return;
        }

        $tenant = new \Slides\Saml2\Models\Tenant([
            'key' => $key,
            'uuid' => \Ramsey\Uuid\Uuid::uuid4(),
            'idp_entity_id' => $entityId,
            'idp_login_url' => $loginUrl,
            'idp_logout_url' => $logoutUrl,
            'metadata' => $metadata,
        ]);

        if(!$tenant->save()) {
            $this->error('Tenant cannot be saved.');
            return;
        }

        $this->info("The tenant #{$tenant->id} ({$tenant->uuid}) was successfully created.");

        $this->output->section('Credentials for the tenant');
        $this->output->text([
            'Identifier (Entity ID): <comment>' . route('saml.metadata', ['uuid' => $tenant->uuid]) . '</comment>',
            'Reply URL (Assertion Consumer Service URL): <comment>' . route('saml.acs', ['uuid' => $tenant->uuid]) . '</comment>',
            'Sign on URL: <comment>' . route('saml.login', ['uuid' => $tenant->uuid]) . '</comment>',
            'Logout URL: <comment>' . route('saml.logout', ['uuid' => $tenant->uuid]) . '</comment>',
            'Relay State: <comment>' . config('saml2.loginRoute') . ' (optional)</comment>'
        ]);

        $this->output->newLine();
    }
}