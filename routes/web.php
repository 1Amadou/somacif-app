<?php

use App\Http\Controllers\PageController;
use App\Livewire\Auth\MagicLogin;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\CheckoutPage; 
use App\Livewire\Livreur\DashboardPage as LivreurDashboard;
use App\Livewire\Livreur\ShowOrderPage as LivreurShowOrder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Client\ShowOrderPage as ClientShowOrder;
use App\Livewire\Client\EditOrderPage as ClientEditOrder;
use App\Http\Controllers\InvoiceController;



/*
|--------------------------------------------------------------------------
| Routes du Site Web
|--------------------------------------------------------------------------
*/

// --- ROUTES DE FACTURATION (Protégées) ---

Route::get('/admin/orders/{order}/invoice', [InvoiceController::class, 'downloadOrderInvoice'])
    ->middleware('auth') // Seuls les admins connectés peuvent y accéder
    ->name('invoice.order');

    Route::get('/invoices/order/{order}', [InvoiceController::class, 'generateOrderInvoice'])
    ->name('invoices.order')
    ->middleware('auth'); // On protège la route

Route::get('/invoices/delivery-note/{order}', [InvoiceController::class, 'generateDeliveryNote'])
    ->name('invoices.delivery-note')
    ->middleware('auth'); // On protège la route

Route::get('/invoices/reglement-receipt/{reglement}', [InvoiceController::class, 'generateReglementReceipt'])
    ->name('invoices.reglement-receipt')
    ->middleware('auth');
    
Route::get('/reports/arrivage/{arrivage}', [InvoiceController::class, 'generateArrivageReport'])
    ->name('reports.arrivage')
    ->middleware('auth');


// --- AUTHENTIFICATION & DÉCONNEXION ---
Route::get('/connexion', MagicLogin::class)->middleware('guest:client,livreur')->name('login');
Route::post('/deconnexion', function () {
    $guard = Auth::guard('client')->check() ? 'client' : 'livreur';
    Auth::guard($guard)->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// --- ESPACES PROTÉGÉS ---
// --- ESPACE CLIENT (Protégé) ---
Route::prefix('client')->name('client.')->middleware(['auth:client'])->group(function () {
    Route::get('/tableau-de-bord', ClientDashboard::class)->name('dashboard');
    Route::get('/finaliser-commande', CheckoutPage::class)->name('checkout');
    Route::get('/commandes/{order}', ClientShowOrder::class)->name('orders.show');
    Route::get('/commandes/{order}/modifier', ClientEditOrder::class)->name('orders.edit');
    Route::get('/commandes/{order}/facture', [InvoiceController::class, 'downloadOrderInvoice'])->name('orders.invoice');
    Route::get('/reglements/{reglement}/bordereau', [InvoiceController::class, 'downloadReglementPdf'])->name('reglements.bordereau');
});

// --- ESPACE LIVREUR (Protégé) ---
Route::prefix('livreur')->name('livreur.')->middleware(['auth:livreur'])->group(function () {
    Route::get('/tableau-de-bord', LivreurDashboard::class)->name('dashboard');
    Route::get('/commandes/{order}', LivreurShowOrder::class)->name('orders.show');
});

// --- PAGES PUBLIQUES ET CONTENU ---
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/societe', [PageController::class, 'societe'])->name('societe');
Route::get('/nos-offres', [PageController::class, 'nosOffres'])->name('nos-offres');
Route::get('/points-de-vente', [PageController::class, 'pointsDeVente'])->name('points-de-vente');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/devenir-partenaire', [PageController::class, 'devenirPartenaire'])->name('devenir-partenaire');

// CORRECTION : Routes pour les pages "landing" des clients
Route::get('/grossistes', [PageController::class, 'grossistes'])->name('grossistes');
Route::get('/hotels-restaurants', [PageController::class, 'hotelsRestaurants'])->name('hotels-restaurants');
Route::get('/particuliers', [PageController::class, 'particuliers'])->name('particuliers');

// Routes pour le catalogue et les produits
Route::get('/produits', [PageController::class, 'products'])->name('products.index');
Route::get('/produits/{product:slug}', [PageController::class, 'productShow'])->name('products.show');

// Routes pour les actualités
Route::get('/actualites', [PageController::class, 'actualites'])->name('actualites.index');
Route::get('/actualites/{post:slug}', [PageController::class, 'actualiteShow'])->name('actualites.show');

// Route "attrape-tout" pour les pages légales (Doit être en dernier)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');