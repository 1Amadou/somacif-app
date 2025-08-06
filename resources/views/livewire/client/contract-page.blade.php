<div class="py-24">
    <div class="container mx-auto px-6 max-w-4xl">
        <h1 class="text-5xl font-teko uppercase text-white">Contrat & Conditions de Partenariat</h1>
        <p class="text-slate-400">Veuillez lire attentivement les documents ci-dessous.</p>

        @if (session('success'))
            <div class="mt-8 bg-green-900/50 border border-green-700 text-green-300 p-4 rounded-lg text-center">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 space-y-8">
            <div class="bg-dark-card border border-border-dark rounded-lg p-8">
                <h2 class="text-3xl font-teko text-white mb-4">Votre Contrat Partenaire</h2>
                @if($client->contract_path)
                    <p class="text-slate-300 mb-4">Votre contrat signé, téléversé par notre équipe, est disponible au téléchargement.</p>
                    <a href="{{ Storage::url($client->contract_path) }}" target="_blank" class="inline-block btn btn-primary py-3 px-6">
                        <i class="fas fa-download mr-2"></i> Télécharger mon contrat (PDF)
                    </a>
                @else
                    <p class="text-slate-400">Aucun contrat n'a encore été téléversé par l'administration. Veuillez contacter votre agent commercial.</p>
                @endif
            </div>

            <div class="bg-dark-card border border-border-dark rounded-lg p-8">
                <h2 class="text-3xl font-teko text-white mb-4">Conditions Générales d'Utilisation</h2>
                <div class="prose prose-invert max-w-none text-slate-400 h-64 overflow-y-auto border border-slate-700 p-4 rounded-md">
                    {!! $cguContent !!}
                </div>
            </div>

            @if(!$client->terms_accepted_at)
                <div class="bg-slate-900/50 border border-brand-red rounded-lg p-8">
                    <form wire:submit.prevent="acceptTerms">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms_accepted" wire:model="terms_accepted" type="checkbox" class="focus:ring-primary h-4 w-4 text-primary border-slate-600 rounded bg-slate-700">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms_accepted" class="font-medium text-white">Je reconnais avoir lu et j'accepte les Conditions Générales d'Utilisation.</label>
                                @error('terms_accepted') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" wire:loading.attr="disabled" class="btn bg-green-600 hover:bg-green-700 disabled:opacity-50">
                                Valider et Accepter Définitivement
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-green-900/50 border border-green-700 text-green-300 p-6 rounded-lg text-center">
                    <p class="font-bold"><i class="fas fa-check-circle mr-2"></i>Vous avez accepté les conditions le {{ $client->terms_accepted_at->format('d/m/Y à H:i') }}.</p>
                </div>
            @endif
        </div>
    </div>
</div>