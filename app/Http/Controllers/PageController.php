<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\PointDeVente;
use App\Models\Post;
use App\Models\Product;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Affiche la page d'accueil.
     */
    public function home()
    {
        $page = Page::where('slug', 'accueil')->firstOrFail();
        $featuredProducts = Product::where('is_visible', true)->latest()->take(4)->get();
        $latestPosts = Post::where('status', 'published')->latest('published_at')->take(2)->get();
        
        return view('welcome', [
            'page' => $page,
            'featuredProducts' => $featuredProducts,
            'latestPosts' => $latestPosts,
        ]);
    }

    /**
     * Affiche une page statique générique par son slug.
     * Gère "societe", "nos-offres", "contact", etc.
     */
     /**
     * Affiche une page statique générique.
     */
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $viewName = 'pages.' . $slug;

        if (!view()->exists($viewName)) {
            return view('pages.legal', ['page' => $page]); // Utilisez une vue existante
        }

        return view($viewName, ['page' => $page]);
    }
    
    // NOTE : Pour garder le code simple, j'ai utilisé la méthode `show` ci-dessus.
    // Les méthodes ci-dessous sont gardées pour correspondre à vos routes, mais elles font la même chose.
    public function societe() { return $this->show('societe'); }
    public function nosOffres() { return $this->show('nos-offres'); }
    public function pointsDeVente() { return $this->show('points-de-vente'); }
    public function contact() { return $this->show('contact'); }
    public function devenirPartenaire() { return $this->show('devenir-partenaire'); }

    /**
     * Affiche la page du catalogue de produits.
     */
    public function catalogue(): View
    {
        $page = Page::where('slug', 'catalogue')->firstOrFail();
        $products = Product::where('is_visible', true)->paginate(12);
        $authenticatedClient = auth('client')->user();

        return view('pages.products.index', compact('page', 'products', 'authenticatedClient'));
    }

    /**
     * Affiche la page de détail d'un produit.
     */
    public function showProduct(Product $product): View
    {
        $relatedProducts = Product::where('id', '!=', $product->id)->inRandomOrder()->limit(4)->get();
        $authenticatedClient = auth('client')->user();

        return view('pages.products.show', compact('product', 'relatedProducts', 'authenticatedClient'));
    }
    
    /**
     * Affiche la liste des actualités.
     */
    public function actualites()
    {
        $page = Page::where('slug', 'actualites')->firstOrFail();
        $posts = Post::where('status', 'published')->latest('published_at')->paginate(9);
        $categories = Category::whereHas('posts', function ($query) {
            $query->where('status', 'published');
        })->get();

        return view('pages.actualites.index', [
            'page' => $page, 
            'posts' => $posts, 
            'categories' => $categories
        ]);
    }

    /**
     * Affiche un article d'actualité spécifique.
     */
    public function actualiteShow(string $slug)
    {
        $post = Post::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $recentPosts = Post::where('status', 'published')->where('id', '!=', $post->id)->latest('published_at')->take(5)->get();
        
        return view('pages.actualites.show', [
            'post' => $post, 
            'recentPosts' => $recentPosts
        ]);
    }
}