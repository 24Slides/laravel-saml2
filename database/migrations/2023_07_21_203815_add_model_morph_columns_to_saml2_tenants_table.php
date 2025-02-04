<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModelMorphColumnsToSaml2TenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('saml2_tenants', 'saml2_identity_providers');

        Schema::table('saml2_identity_providers', function (Blueprint $table) {
            $table->nullableMorphs('tenant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('saml2_identity_providers', function (Blueprint $table) {
            $table->dropMorphs('tenant');
        });

        Schema::rename('saml2_identity_providers', 'saml2_tenants');
    }
}
