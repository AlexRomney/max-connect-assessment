<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MaxConnectController;

Route::get('/', MaxConnectController::class)->name('home');
