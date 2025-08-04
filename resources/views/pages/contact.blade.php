<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? 'Contactez-nous - SOMACIF')

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Contact' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? '' }}</h1>
            <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
        </div>
    </section>

    <section class="py-24">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-3 gap-12">
                
                <div class="lg:col-span-1">
                    <h2 class="text-4xl uppercase text-white mb-6">Informations</h2>
                    <div class="space-y-6">
                        <div class="contact-card p-6 rounded-lg">
                            <h3 class="text-xl font-bold text-white mb-2 flex items-center"><i class="fas fa-phone brand-red mr-3"></i> Appelez-nous</h3>
                            <a href="tel:+22376000000" class="text-lg text-white mt-2 inline-block hover:brand-red">+223 76 00 00 00</a>
                        </div>
                        <div class="contact-card p-6 rounded-lg">
                            <h3 class="text-xl font-bold text-white mb-2 flex items-center"><i class="fab fa-whatsapp brand-red mr-3"></i> WhatsApp</h3>
                            <a href="https://wa.me/22376000001" class="text-lg text-white mt-2 inline-block hover:brand-red">+223 76 00 00 01</a>
                        </div>
                        <div class="contact-card p-6 rounded-lg">
                            <h3 class="text-xl font-bold text-white mb-2 flex items-center"><i class="fas fa-envelope brand-red mr-3"></i> Email</h3>
                            <a href="mailto:contact@somacif.ml" class="text-lg text-white mt-2 inline-block hover:brand-red">contact@somacif.ml</a>
                        </div>
                         <div class="contact-card p-6 rounded-lg">
                            <h3 class="text-xl font-bold text-white mb-2 flex items-center"><i class="fas fa-map-marker-alt brand-red mr-3"></i> Siège Social</h3>
                            <p class="text-slate-400">Halle de Bamako, Rue 123, Bamako</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <h2 class="text-4xl uppercase text-white mb-6">Envoyez-nous un message</h2>
                    <livewire:contact-form />
                </div>

            </div>
        </div>
    </section>
</x-layouts.app>