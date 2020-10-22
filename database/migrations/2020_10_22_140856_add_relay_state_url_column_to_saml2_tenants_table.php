<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelayStateUrlColumnToSaml2TenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saml2_tenants', function (Blueprint $table) {
            $table->string('relay_state_url')->nullable();
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
            $table->dropColumn('relay_state_url');
        });
    }
}
