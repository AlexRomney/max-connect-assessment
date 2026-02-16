<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MaxConnectController;

Route::match(['get', 'post'], '/', MaxConnectController::class)->name('home');
