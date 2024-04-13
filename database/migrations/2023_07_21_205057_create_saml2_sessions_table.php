<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaml2SessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saml2_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idp_id')->constrained('saml2_identity_providers');
            $table->foreignId('user_id')->nullable();
            $table->json('payload');
            $table->timestamps('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saml2_sessions');
    }
}
