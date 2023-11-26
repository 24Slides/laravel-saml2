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
        Schema::table('saml2_tenants', function (Blueprint $table) {
            $table->nullableMorphs('authenticatable');
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
            $table->dropMorphs('authenticatable');
        });
    }
}
