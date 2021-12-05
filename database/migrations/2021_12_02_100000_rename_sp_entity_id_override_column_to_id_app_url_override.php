<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSpEntityIdOverrideColumnToIdAppUrlOverride extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saml2_tenants', function (Blueprint $table) {
            $table->renameColumn('sp_entity_id_override', 'id_app_url_override');
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
            $table->renameColumn('id_app_url_override', 'sp_entity_id_override');
        });
    }
}
