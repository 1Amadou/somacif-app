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
     */
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $viewName = 'pages.' . $slug;

        if (!view()->exists($viewName)) {
            // Si la vue n'existe pas, on peut utiliser une vue existante comme fallback
            // J'ai renommé 'pages.legal' en 'pages.show' pour plus de généricité.
            return view('pages.show', ['page' => $page]);
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
    public function grossistes() { return $this->show('grossistes'); }
    
    /**
     * Affiche la page pour les particuliers.
     */
    public function particuliers(): View
    {
        $page = Page::where('slug', 'particuliers')->firstOrFail();
        // Assumons que vous avez un modèle pour les points de vente
        // $pointsDeVente = PointDeVente::where('is_active', true)->get();
        return view('pages.particuliers', compact('page'));
    }

    /**
     * Affiche la page pour les hôtels et restaurants.
     */
    public function hotelsRestaurants(): View
    {
        return $this->show('hotels-restaurants');
    }

    /**
     * Affiche la page du catalogue de produits.
     */
    public function catalogue(): View
    {
        $page = Page::where('slug', 'catalogue')->firstOrFail();
        $products = Product::where('is_visible', true)->paginate(12);

        // La variable $authenticatedClient n'est pas utilisée dans la vue et peut être retirée.
        return view('pages.products.index', compact('page', 'products'));
    }

     /**
     * NOUVEAU : Affiche le catalogue de produits.
     */
    public function products()
    {
        $page = Page::where('slug', 'produits')->firstOrFail();
        $products = Product::where('is_visible', true)->latest()->paginate(12);
        return view('pages.products.index', compact('page', 'products'));
    }

    /**
     * NOUVEAU : Affiche un produit spécifique.
     * Grâce au "Route Model Binding", Laravel trouve le produit automatiquement à partir du slug dans l'URL.
     */
    public function productShow(Product $product)
    {
        if (!$product->is_visible) {
            abort(404);
        }
        $relatedProducts = Product::where('is_visible', true)->where('id', '!=', $product->id)->inRandomOrder()->take(4)->get();
        return view('pages.products.show', compact('product', 'relatedProducts'));
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