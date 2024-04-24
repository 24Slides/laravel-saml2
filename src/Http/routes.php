<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('saml2.routesPrefix'),
    'middleware' => array_merge(['saml2.resolveTenant'], config('saml2.routesMiddleware')),
], function () {
    $saml2_controller = config('saml2.saml2_controller', 'Slides\Saml2\Http\Controllers\Saml2Controller');

    Route::get('/{uuid}/logout', array(
        'as' => 'saml.logout',
        'uses' => $saml2_controller.'@logout',
    ));

    Route::get('/{uuid}/login', array(
        'as' => 'saml.login',
        'uses' => $saml2_controller.'@login',
    ));

    Route::get('/{uuid}/metadata', array(
        'as' => 'saml.metadata',
        'uses' => $saml2_controller.'@metadata',
    ));

    Route::post('/{uuid}/acs', array(
        'as' => 'saml.acs',
        'uses' => $saml2_controller.'@acs',
    ));

    Route::get('/{uuid}/sls', array(
        'as' => 'saml.sls',
        'uses' => $saml2_controller.'@sls',
    ));
});
