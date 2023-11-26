<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNullableIdpColumnsInSaml2TenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saml2_tenants', function (Blueprint $table) {
            $table->string('idp_entity_id')->nullable()->change();
            $table->string('idp_login_url')->nullable()->change();
            $table->string('idp_logout_url')->nullable()->change();
            $table->string('name_id_format')->nullable()->change();
            $table->text('idp_x509_cert')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('saml2_tenants', function (Blueprint $table) {
            $table->string('idp_entity_id')->nullable(false)->change();
            $table->string('idp_login_url')->nullable(false)->change();
            $table->string('idp_logout_url')->nullable(false)->change();
            $table->string('name_id_format')->nullable(false)->change();
            $table->text('idp_x509_cert')->nullable(false)->change();
        });
    }
}
