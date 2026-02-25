@php
    $limit = $limit ?? null;
    $offset = $offset ?? 0;
    $infinite = $infinite ?? false;
    $allPages = $pages ?? collect();

    if (!is_null($limit)) {
        $displayPages = $allPages->slice($offset, $limit);
    } else {
        $displayPages = $allPages->slice($offset);
    }

    // Offset global untuk JS (dipakai hanya jika infinite scroll aktif)
    $initialOffset = $offset + $displayPages->count();
@endphp

<div @if($infinite) id="card-list" @endif
    class="max-w-screen-xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-y-20 px-5">
    @include('front.components.card-items', ['pages' => $displayPages])
</div>

@if($infinite)
    <div id="loading-spinner" class="w-full flex justify-center py-6 hidden">
        <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    @push('scripts')
        <script>
            let loading = false;
            let offset = {{ $initialOffset }};
            const limit = 6;
            let finished = false;

            const loadMoreUrl = "{{ route('articles.load-more', ['locale' => app()->getLocale()]) }}";

            window.addEventListener('scroll', function () {
                const spinner = document.getElementById('loading-spinner');
                const cardList = document.getElementById('card-list');

                if (!spinner || !cardList) return;
                if (finished || loading) return;

                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
                    loading = true;
                    spinner.classList.remove('hidden');

                    const url = `${loadMoreUrl}?offset=${offset}&limit=${limit}`;

                    // Tambah sedikit delay supaya animasi loading terlihat
                    setTimeout(() => {
                        fetch(url)
                            .then(res => res.text())
                            .then(html => {
                                const trimmed = html.trim();

                                if (trimmed !== '') {
                                    cardList.insertAdjacentHTML('beforeend', trimmed);

                                    const tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = trimmed;
                                    const newCards = tempDiv.querySelectorAll('.flex.flex-col.gap-2');

                                    offset += newCards.length;

                                    if (newCards.length < limit) {
                                        finished = true;
                                        spinner.classList.add('hidden');
                                    }
                                } else {
                                    finished = true;
                                    spinner.classList.add('hidden');
                                }

                                loading = false;
                            })
                            .catch(() => {
                                spinner.classList.add('hidden');
                                loading = false;
                            });
                    }, 700); // 0.7 detik, bisa diubah sesuai selera
                }
            });
        </script>
    @endpush
@endif