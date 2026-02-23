// Infinite scroll for card-list
let loading = false;
let offset = 6;
const limit = 6;

window.addEventListener('DOMContentLoaded', () => {
    const cardListContainer = document.querySelectorAll('[data-infinite-card-list]');
    if (!cardListContainer.length) return;

    window.addEventListener('scroll', async () => {
        if (loading) return;
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 300) {
            loading = true;
            const response = await fetch(`/load-more-articles?offset=${offset}&limit=${limit}`);
            if (response.ok) {
                const html = await response.text();
                cardListContainer[cardListContainer.length-1].insertAdjacentHTML('beforeend', html);
                offset += limit;
            }
            loading = false;
        }
    });
});
