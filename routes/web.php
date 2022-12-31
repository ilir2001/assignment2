<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'users'], function ($router) {
    $router->post('/register', 'UserController@register');
    $router->post('/login', 'UserController@login');
    $router->post('/logout', 'UserController@logout');
    $router->post('/enrollCourse/{id}', 'UserController@enrollCourse');
    $router->post('/replyOnThread/{id}', 'UserController@replyOnThread');



});

$router->group(['prefix' => 'admins'], function () use ($router) {
    $router->post('/register','AdminController@register');
    $router->post('/login','AdminController@login');
    $router->post('/logout','AdminController@register');
    $router->post('/deleteCourse/{id}','AdminController@deleteCourse');

});
$router->group(['prefix' => 'instructors'], function () use ($router) {
    $router->post('/register','InstructorController@register');
    $router->post('/login','InstructorController@login');
    $router->post('/logout','InstructorController@register');
    $router->post('/createCourse','InstructorController@createCourse');
    $router->post('/createThread/{id}','InstructorController@createThread');




});



