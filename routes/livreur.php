<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LivreurLoginController;

// La route de traitement du formulaire de connexion
// Nom final sera 'livreur.login'
Route::post('/login', [LivreurLoginController::class, 'login'])->name('login');

// La route de déconnexion
// Nom final sera 'livreur.logout'
Route::post('/logout', [LivreurLoginController::class, 'logout'])->name('logout');

// Routes protégées pour les livreurs connectés
Route::middleware(['auth:livreur'])->group(function () {
    // Nom final sera 'livreur.dashboard'
    Route::get('/dashboard', \App\Livewire\Livreur\DashboardPage::class)->name('dashboard');
    // Nom final sera 'livreur.orders.show'
    Route::get('/commandes/{order}', \App\Livewire\Livreur\ShowOrderPage::class)->name('orders.show');
});