<?php

namespace Slides\Saml2\Commands;

use Cerbero\CommandValidator\ValidatesInput;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Unique;
use Slides\Saml2\Helpers\ConsoleHelper;
use Slides\Saml2\Repositories\IdentityProviderRepository;
use Ramsey\Uuid\Uuid;

class Create extends \Illuminate\Console\Command
{
    use RendersTenants, ValidatesInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml2:idp-create
                            { --key= : Key/name of the Identity Provider }
                            { --entityId= : IdP Issuer URL }
                            { --loginUrl= : IdP Sign on URL }
                            { --logoutUrl= : IdP Logout URL }
                            { --relayStateUrl= : Redirection URL after successful login }
                            { --nameIdFormat= : Name ID Format ("persistent" by default) }
                            { --x509cert= : x509 certificate (base64) }
                            { --metadata= : A custom metadata }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an Identity Provider';

    /**
     * @var IdentityProviderRepository
     */
    protected IdentityProviderRepository $tenants;

    /**
     * DeleteTenant constructor.
     *
     * @param IdentityProviderRepository $tenants
     */
    public function __construct(IdentityProviderRepository $tenants)
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
        $metadata = ConsoleHelper::stringToArray($this->option('metadata'));

        $model = config('saml2.idpModel');
        $tenant = new $model([
            'key' => $this->option('key'),
            'uuid' => Uuid::uuid4(),
            'idp_entity_id' => $this->option('entityId'),
            'idp_login_url' => $this->option('loginUrl'),
            'idp_logout_url' => $this->option('logoutUrl'),
            'idp_x509_cert' => $this->option('x509cert'),
            'relay_state_url' => $this->option('relayStateUrl'),
            'name_id_format' => $this->option('nameIdFormat'),
            'metadata' => $metadata,
        ]);

        if(!$tenant->save()) {
            $this->error('IdentityProvidable cannot be saved.');
            return;
        }

        $this->info("The tenant #$tenant->id ($tenant->uuid) was successfully created.");

        $this->renderTenantCredentials($tenant);

        $this->output->note('You can share this info with the Identity Provider to retrieve the metadata, and then finish the setup by running:');
        $this->output->block(
            sprintf('php artisan saml2:idp-update %d \
                --entityId="%s" \
                --loginUrl="%s" \
                --logoutUrl="%s" \
                --x509cert="%s"',
                $tenant->id,
                '(received Entity ID)',
                '(received SSO URL)',
                '(received Logout URL)',
                '(received x509 certificate)'
            )
        );
    }

    /**
     * Validation rules for the user input.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'key' => ['string', new Unique(config('saml2.idpModel'), 'key')],
            'entityId' => 'string',
            'loginUrl' => 'string|url',
            'logoutUrl' => 'string|url',
            'x509cert' => 'string',
            'relayStateUrl' => 'string|url',
            'metadata' => 'string',
            'nameIdFormat' => ['string', new In($this->nameIdFormatValues())]
        ];
    }

    /**
     * Get the values for the name ID format rule.
     *
     * @return string[]
     */
    protected function nameIdFormatValues(): array
    {
        return [
            'persistent',
            'transient',
            'emailAddress',
            'unspecified',
            'X509SubjectName',
            'WindowsDomainQualifiedName',
            'kerberos',
            'entity'
        ];
    }
}
