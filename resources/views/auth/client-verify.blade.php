<x-layouts.app>
    <div class="py-24 flex items-center justify-center">
        <div class="bg-dark-card border border-border-dark rounded-lg p-8 w-full max-w-md">
            <h1 class="text-4xl font-teko uppercase text-white text-center mb-6">Vérification de Sécurité</h1>
            <p class="text-slate-400 text-center mb-6">Un code a été envoyé par SMS.</p>
            @if(session('test_code'))
                <p class="text-center text-green-400 mb-4">Mode Test - Votre code est : {{ session('test_code') }}</p>
            @endif
            <form method="POST" action="{{ route('client.login.verify') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="verification_code" class="form-label">Code de Vérification</label>
                    <input type="text" id="verification_code" name="verification_code" class="form-input text-center text-2xl tracking-[1em]" required>
                    @error('verification_code')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full btn btn-primary py-3">Confirmer et se connecter</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>