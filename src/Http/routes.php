<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('saml2.routesPrefix'),
    'middleware' => array_merge(['saml2.resolveIdentityProvider'], config('saml2.routesMiddleware')),
], function () {
    Route::get('/{uuid}/logout', array(
        'as' => 'saml.logout',
        'uses' => 'Slides\Saml2\Http\Controllers\Saml2Controller@logout',
    ))->whereUuid('uuid');

    Route::get('/{uuid}/login', array(
        'as' => 'saml.login',
        'uses' => 'Slides\Saml2\Http\Controllers\Saml2Controller@login',
    ))->whereUuid('uuid');

    Route::get('/{uuid}/metadata', array(
        'as' => 'saml.metadata',
        'uses' => 'Slides\Saml2\Http\Controllers\Saml2Controller@metadata',
    ))->whereUuid('uuid');

    Route::post('/{uuid}/acs', array(
        'as' => 'saml.acs',
        'uses' => 'Slides\Saml2\Http\Controllers\Saml2Controller@acs',
    ))->whereUuid('uuid');

    Route::get('/{uuid}/sls', array(
        'as' => 'saml.sls',
        'uses' => 'Slides\Saml2\Http\Controllers\Saml2Controller@sls',
    ))->whereUuid('uuid');
});
