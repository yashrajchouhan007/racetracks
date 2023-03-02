<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// User API's
Route::group([
    'prefix' => 'auth'
], function () {
  Route::group([
      'middleware' => ['cors'] // cors, forced
    ], function() {
      Route::post('login', 'AuthController@login')->name('login.api');
      Route::post('signup','AuthController@signup')->name('signup.api');
  });
});

// Admin API's
Route::group([
    'prefix' => 'admin'
], function () {
  Route::group([
      'middleware' => ['cors'] // 'auth:api' cors headers, authentication by passport
    ], function() {
      Route::get('categories', 'MasterController@categories')->name('categories.api');
      Route::get('subcategories', 'MasterController@subcategories')->name('subcategories.api');
      Route::get('types', 'MasterController@types')->name('types.api');
      Route::get('racetracks', 'MasterController@racetracks')->name('racetracks.api');
      Route::post('add_category', 'MasterController@add_category')->name('add_category.api');
      Route::post('update_category', 'MasterController@update_category')->name('update_category.api');
      Route::post('add_subcategory', 'MasterController@add_subcategory')->name('add_subcategory.api');
      Route::post('update_subcategory', 'MasterController@update_subcategory')->name('update_subcategory.api');
      Route::post('add_type', 'MasterController@add_type')->name('add_type.api');
      Route::post('update_type', 'MasterController@update_type')->name('update_type.api');
      Route::post('add_racetrack', 'MasterController@add_racetrack')->name('add_racetrack.api');
      Route::post('update_racetrack', 'MasterController@update_racetrack')->name('update_racetrack.api');
      Route::get('delete_racetrack', 'MasterController@delete_racetrack')->name('delete_racetrack.api');
      Route::post('change_availability', 'MasterController@change_availability')->name('change_availability.api');
      Route::post('change_status', 'MasterController@change_status')->name('change_status.api');
      Route::post('add_equipment', 'MasterController@add_equipment')->name('add_equipment.api');
      Route::post('update_equipment', 'MasterController@update_equipment')->name('update_equipment.api');
      Route::get('equipments', 'MasterController@equipments')->name('equipments.api');
      Route::post('add_serviceman', 'MasterController@add_serviceman')->name('add_serviceman.api');
      Route::post('update_serviceman', 'MasterController@update_serviceman')->name('update_serviceman.api');
      Route::get('servicemans', 'MasterController@servicemans')->name('servicemans.api');
      Route::get('users', 'MasterController@users')->name('users.api');
      Route::post('create_user', 'MasterController@add_user')->name('create_user.api');
      Route::post('update_user', 'MasterController@update_user')->name('update_user.api');
      Route::get('delete_user', 'MasterController@delete_user')->name('delete_user.api');
      Route::get('delete_type', 'MasterController@delete_type')->name('delete_type.api');
      Route::get('delete_equipment', 'MasterController@delete_equipment')->name('delete_equipment.api');

      Route::get('details', 'MasterController@details')->name('details.api');
      Route::post('add-city', 'MasterController@add_city')->name('add-city.api');
      Route::post('cities', 'MasterController@cities')->name('cities.api');
      Route::get('states', 'MasterController@states')->name('states.api');
      Route::get('countries', 'MasterController@countries')->name('countries.api');
      Route::get('update_user_status', 'MasterController@update_user_status')->name('update_user_status.api');
    });
});

// Racetrack Owner Account API's
Route::group([
    'prefix' => 'owner'
], function () {
  Route::group([
      'middleware' => ['cors'] // 'auth:api' cors headers, authentication by passport
    ], function() {
      Route::get('list', 'RacetrackOwnerController@racetracks')->name('list.api');
      Route::post('change_availability', 'RacetrackOwnerController@change_availability')->name('change_availability.api');
      Route::post('add_racetrack', 'RacetrackOwnerController@add_racetrack')->name('add_racetrack.api');
      Route::post('update_racetrack', 'RacetrackOwnerController@update_racetrack')->name('update_racetrack.api');
      Route::post('add_equipment', 'RacetrackOwnerController@add_equipment')->name('add_equipment.api');
      Route::post('update_equipment', 'RacetrackOwnerController@update_equipment')->name('update_equipment.api');
      Route::post('add_serviceman', 'RacetrackOwnerController@add_serviceman')->name('add_serviceman.api');
      Route::post('update_serviceman', 'RacetrackOwnerController@update_serviceman')->name('update_serviceman.api');
      Route::post('create_user', 'RacetrackOwnerController@add_user')->name('create_user.api');
    });
});

// Equipment Account API's
Route::group([
    'prefix' => 'equipment'
], function () {
  Route::group([
      'middleware' => ['cors'] // 'auth:api' cors headers, authentication by passport
    ], function() {
      Route::post('add_equipment', 'EquipmentController@add_equipment')->name('add_equipment.api');
      Route::post('update_equipment', 'EquipmentController@update_equipment')->name('update_equipment.api');
      Route::get('list', 'EquipmentController@equipments')->name('list.api');
      Route::post('change_status', 'EquipmentController@change_eq_status')->name('change_status.api');
      Route::get('details', 'EquipmentController@eq_details')->name('details.api');
      Route::post('add_rtequipment', 'EquipmentController@add_equipment_to_racetrack')->name('add_rtequipment.api');
      //
    });
});

// Serviceman Account API's
Route::group([
    'prefix' => 'serviceman'
], function () {
  Route::group([
      'middleware' => ['cors'] // 'auth:api' cors headers, authentication by passport
    ], function() {
      Route::post('add_serviceman', 'ServicemanController@add_serviceman')->name('add_serviceman.api');
      Route::post('update_serviceman', 'ServicemanController@update_serviceman')->name('update_serviceman.api');
      Route::get('list', 'ServicemanController@servicemans')->name('list.api');
      Route::post('change_status', 'ServicemanController@change_sm_status')->name('change_status.api');
      Route::get('details', 'ServicemanController@sm_details')->name('details.api');
      Route::post('add_rtserviceman', 'ServicemanController@add_sm_to_racetrack')->name('add_rtserviceman.api');
      Route::get('delete_serviceman', 'ServicemanController@delete_serviceman')->name('delete_serviceman.api');
      // specialization Routes
      // Route::get('specializations', 'ServicemanController@specializations')->name('specializations.api');


    });
});

// Front Page API Routes
Route::get('get-rts', 'FrontPageController@index')->name('get-rts.api');
Route::get('get-featured-rts', 'FrontPageController@featured_racetracks')->name('get-featured-rts.api');
Route::get('details/{racetrack_id}', 'FrontPageController@racetrack_details')->name('details.api');
Route::post('contact-us', 'FrontPageController@contact_us')->name('contact_us.api');

