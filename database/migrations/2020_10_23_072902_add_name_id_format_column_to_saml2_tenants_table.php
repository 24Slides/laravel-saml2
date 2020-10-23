<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameIdFormatColumnToSaml2TenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saml2_tenants', function (Blueprint $table) {
            $table->string('name_id_format')->default('persistent');
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
            $table->dropColumn('name_id_format');
        });
    }
}
