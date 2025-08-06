<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ClientLoginController;
use App\Http\Controllers\InvoiceController;
use App\Models\Page;
use App\Models\Product;
use App\Models\Post;
use App\Models\PointDeVente;
use App\Models\Order;
use App\Models\Category;

/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $page = Page::where('slug', 'accueil')->firstOrFail();
    $featuredProducts = Product::where('is_visible', true)->latest()->take(4)->get();
    $latestPosts = Post::where('is_published', true)->latest('date_publication')->take(2)->get();
    
    return view('welcome', [
        'page' => $page,
        'featuredProducts' => $featuredProducts,
        'latestPosts' => $latestPosts,
    ]);
})->name('home');
Route::get('/societe', function () { $page = Page::where('slug', 'societe')->firstOrFail(); return view('pages.societe', ['page' => $page]); })->name('societe');
Route::get('/produits', function () { $page = Page::where('slug', 'produits')->firstOrFail(); $products = Product::where('is_visible', true)->latest()->paginate(12); return view('pages.products.index', ['page' => $page, 'products' => $products]); })->name('products.index');
Route::get('/produits/{product:slug}', function (Product $product) { if (!$product->is_visible) { abort(404); } $relatedProducts = Product::where('is_visible', true)->where('id', '!=', $product->id)->inRandomOrder()->take(4)->get(); return view('pages.products.show', ['product' => $product, 'relatedProducts' => $relatedProducts]); })->name('products.show');
Route::get('/actualites', function () { $page = Page::where('slug', 'actualites')->firstOrFail(); $posts = Post::where('is_published', true)->latest('date_publication')->paginate(9); $categories = Category::whereHas('posts', function ($query) { $query->where('is_published', true); })->get(); return view('pages.actualites.index', ['page' => $page, 'posts' => $posts, 'categories' => $categories]); })->name('actualites.index');
Route::get('/actualites/{post:slug}', function (Post $post) { if (!$post->is_published) { abort(404); } $recentPosts = Post::where('is_published', true)->where('id', '!=', $post->id)->latest('date_publication')->take(5)->get(); return view('pages.actualites.show', ['post' => $post, 'recentPosts' => $recentPosts]); })->name('posts.show');
Route::get('/points-de-vente', function () { $page = Page::where('slug', 'points-de-vente')->firstOrFail(); $pointsDeVente = PointDeVente::orderBy('type')->get(); return view('pages.points-de-vente', ['page' => $page, 'pointsDeVente' => $pointsDeVente]); })->name('points-de-vente');
Route::get('/contact', function () { $page = Page::where('slug', 'contact')->firstOrFail(); return view('pages.contact', ['page' => $page]); })->name('contact');
Route::get('/devenir-partenaire', function () { $page = Page::where('slug', 'nos-offres')->firstOrFail(); return view('pages.devenir-partenaire', ['page' => $page]); })->name('devenir-partenaire');
Route::get('/nos-offres', function () { $page = Page::where('slug', 'nos-offres')->firstOrFail(); return view('pages.nos-offres', ['page' => $page]); })->name('nos-offres');
Route::get('/politique-confidentialite', function () { $page = Page::where('slug', 'politique-confidentialite')->firstOrFail(); return view('pages.legal', ['page' => $page]); })->name('politique');
Route::get('/conditions-generales', function () { $page = Page::where('slug', 'conditions-generales')->firstOrFail(); return view('pages.legal', ['page' => $page]); })->name('cgu');
Route::get('/grossistes', function () {
    $page = Page::where('slug', 'grossistes')->firstOrFail();
    return view('pages.client-landing', ['page' => $page]);
})->name('grossistes');

Route::get('/hotels-restaurants', function () {
    $page = Page::where('slug', 'hotels-restaurants')->firstOrFail();
    return view('pages.client-landing', ['page' => $page]);
})->name('hotels-restaurants');

Route::get('/particuliers', function () {
    return redirect()->route('points-de-vente');
})->name('particuliers');


/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/
// NOUVELLE PASSERELLE DE CONNEXION UNIFIÉE
Route::get('/connexion', function() { return view('auth.login'); })->name('login');

// ROUTES DE TRAITEMENT POUR LE FORMULAIRE CLIENT
Route::post('/connexion/send-code', [ClientLoginController::class, 'sendVerificationCode'])->name('client.login.send_code');
Route::get('/connexion/verifier', [ClientLoginController::class, 'showVerificationForm'])->name('client.login.verify.form');
Route::post('/connexion/verifier', [ClientLoginController::class, 'verify'])->name('client.login.verify');
Route::post('/deconnexion', [ClientLoginController::class, 'logout'])->name('client.logout');


/*
|--------------------------------------------------------------------------
| Espace Client Protégé
|--------------------------------------------------------------------------
*/
Route::middleware(['client.auth'])->group(function () {
    Route::get('/mon-compte', \App\Livewire\Client\Dashboard::class)->name('client.dashboard');
    Route::get('/mon-compte/commandes/{order}', function(Order $order) { if ($order->client_id != session('authenticated_client_id')) { abort(403); } $order->load('livreur', 'orderItems'); return view('pages.client.order-show', ['order' => $order]); })->name('client.orders.show');
    Route::get('/mon-compte/commandes/{order}/modifier', \App\Livewire\Client\EditOrderPage::class)->name('client.orders.edit');
    Route::get('/mon-compte/commandes/{order}/facture', [InvoiceController::class, 'show'])->name('client.orders.invoice');
    Route::get('/mon-compte/contrat-et-conditions', \App\Livewire\Client\ContractPage::class)->name('client.contract');
    Route::get('/commande/finaliser', \App\Livewire\CheckoutPage::class)->name('checkout');
});

// require __DIR__.'/auth.php'; // On s'assure que ceci est commenté ou supprimé