<footer id="contact" class="pt-20 pb-8 bg-black border-t border-slate-800">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
            <div class="md:col-span-2">
                <h3 class="text-4xl font-teko text-white mb-4">SOMA<span class="brand-red">CIF</span></h3>
                <p class="text-slate-400 pr-8">{{ $siteFooter->contenus['description'] ?? '' }}</p>
            </div>
            <div>
                <h3 class="font-teko text-2xl text-white mb-4 uppercase tracking-wider">Liens Rapides</h3>
                <ul class="space-y-2">
                    @if(!empty($siteFooter->contenus['quick_links']))
                        @foreach($siteFooter->contenus['quick_links'] as $link)
                            <li><a href="{{ url($link['url']) }}" class="text-slate-400 hover:text-primary">{{ $link['label'] }}</a></li>
                        @endforeach
                    @endif
                </ul>
            </div>
            <div>
                <h3 class="font-teko text-2xl text-white mb-4 uppercase tracking-wider">Légal</h3>
                <ul class="space-y-2">
                     @if(!empty($siteFooter->contenus['legal_links']))
                        @foreach($siteFooter->contenus['legal_links'] as $link)
                            <li><a href="{{ url($link['url']) }}" class="text-slate-400 hover:text-primary">{{ $link['label'] }}</a></li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 mt-16 pt-6 flex flex-col md:flex-row justify-between items-center text-sm">
            <p class="text-slate-500">&copy; {{ date('Y') }} SOMACIF. Tous droits réservés.</p>
            <div class="flex space-x-4 mt-4 md:mt-0">
                @if(!empty($siteFooter->contenus['social_links']))
                    @foreach($siteFooter->contenus['social_links'] as $link)
                         <a href="{{ $link['url'] }}" target="_blank" class="text-slate-500 hover:text-white"><i class="{{ $link['icon'] }}"></i></a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</footer>