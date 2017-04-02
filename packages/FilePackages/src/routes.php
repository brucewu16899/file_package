<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('file', 'FilePackages\FileController@index');
Route::get('file/download', 'FilePackages\FileController@getDownload');
Route::post('file/upload', 'FilePackages\FileController@postUpload');
Route::post('file/delete', 'FilePackages\FileController@postDelete');
Route::post('file/deletefloder', 'FilePackages\FileController@postDeletefloder');

Route::get('/', 'FilePackages\FileController@index');

