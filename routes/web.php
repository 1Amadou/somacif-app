<?php

use App\Http\Controllers\PageController;
use App\Livewire\Auth\MagicLogin;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Livreur\DashboardPage as LivreurDashboard;
use App\Livewire\Livreur\ShowOrderPage as LivreurShowOrder;
use App\Livewire\ProductCatalog;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du Site Web
|--------------------------------------------------------------------------
*/

// --- AUTHENTIFICATION ---
// La seule porte d'entrée pour la connexion
Route::get('/connexion', MagicLogin::class)->middleware('guest:client,livreur')->name('login');


// --- ESPACE CLIENT (Protégé) ---
Route::prefix('client')->name('client.')->middleware(['auth:client'])->group(function () {
    Route::get('/tableau-de-bord', ClientDashboard::class)->name('dashboard');
    // Ajoutez ici les autres routes du tableau de bord client...
    // exemple: Route::get('/mes-commandes', MesCommandesPage::class)->name('orders.index');
});


// --- ESPACE LIVREUR (Protégé) ---
Route::prefix('livreur')->name('livreur.')->middleware(['auth:livreur'])->group(function () {
    Route::get('/tableau-de-bord', LivreurDashboard::class)->name('dashboard');
    Route::get('/commandes/{order}', LivreurShowOrder::class)->name('orders.show');
    // Ajoutez ici les autres routes du tableau de bord livreur...
});


// --- PAGES PUBLIQUES ET CONTENU ---
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/societe', [PageController::class, 'societe'])->name('societe');
Route::get('/catalogue', ProductCatalog::class)->name('catalogue');
Route::get('/catalogue/{product:slug}', [PageController::class, 'showProduct'])->name('products.show');
Route::get('/nos-offres', [PageController::class, 'nosOffres'])->name('nos-offres');
Route::get('/actualites', [PageController::class, 'actualites'])->name('actualites.index');
Route::get('/actualites/{slug}', [PageController::class, 'actualiteShow'])->name('actualites.show');
Route::get('/points-de-vente', [PageController::class, 'pointsDeVente'])->name('points-de-vente');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/devenir-partenaire', [PageController::class, 'devenirPartenaire'])->name('devenir-partenaire');
Route::get('/grossistes', [PageController::class, 'grossistes'])->name('grossistes');
Route::get('/hotels-restaurants', [PageController::class, 'hotelsRestaurants'])->name('hotels-restaurants');
Route::get('/particuliers', [PageController::class, 'particuliers'])->name('particuliers');

// Route "attrape-tout" pour les pages légales (Doit être en dernier)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');