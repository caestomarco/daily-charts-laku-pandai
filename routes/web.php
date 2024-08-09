<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Dashboard::class)->name('dashboard');

Route::get('/history', \App\Livewire\History::class)->name('history');

Route::get('/agent-management', \App\Livewire\AgentManagement::class)->name('agent-management');

Route::get('/branch-management', \App\Livewire\BranchManagement::class)->name('branch-management');

Route::get('/product-management', \App\Livewire\ProductManagement::class)->name('product-management');