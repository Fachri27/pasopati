import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import flatpickr from 'flatpickr';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const TILE_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const TILE_ATTR =
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

function addTileLayer(map) {
    L.tileLayer(TILE_URL, { maxZoom: 19, attribution: TILE_ATTR }).addTo(map);
}

function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
}

function parseJson(raw) {
    if (!raw) return null;
    try {
        return JSON.parse(raw);
    } catch (e) {
        return null;
    }
}

/* ======================================================
 | Peta detail (halaman show)
 ====================================================== */
function initDetailMap() {
    const container = document.getElementById('event-detail-map');
    if (!container) return;

    const lat = parseFloat(container.dataset.lat);
    const lng = parseFloat(container.dataset.lng);
    if (isNaN(lat) || isNaN(lng)) return;

    const map = L.map(container, { scrollWheelZoom: false }).setView([lat, lng], 11);
    addTileLayer(map);

    const geojson = parseJson(container.dataset.geojson);
    if (geojson && geojson.type) {
        const layer = L.geoJSON(geojson, {
            style: { color: '#2563eb', weight: 2, fillOpacity: 0.1 },
        }).addTo(map);
        map.fitBounds(layer.getBounds(), { padding: [30, 30] });
    }

    L.marker([lat, lng])
        .addTo(map)
        .bindPopup('<strong>' + escapeHtml(container.dataset.location || '') + '</strong>');
}

/* ======================================================
 | Form create/edit
 ====================================================== */
function initForm() {
    const form = document.getElementById('event-form');
    if (!form) return;

    const mapEl = document.getElementById('event-map');
    const resultsEl = document.getElementById('location-results');
    const searchInput = document.getElementById('location-search');
    const locationInput = document.getElementById('location');
    const geojsonInput = document.getElementById('location_geojson');
    const latInput = document.getElementById('location_lat');
    const lngInput = document.getElementById('location_lng');

    let marker = null;
    let polygonLayer = null;

    const initialLat = parseFloat(latInput.value);
    const initialLng = parseFloat(lngInput.value);
    const hasInitial = !isNaN(initialLat) && !isNaN(initialLng);

    const map = L.map(mapEl).setView(
        hasInitial ? [initialLat, initialLng] : [-2.5489, 118.0149],
        hasInitial ? 12 : 5
    );
    addTileLayer(map);

    function setMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
    }

    function setCoords(lat, lng) {
        latInput.value = Number(lat).toFixed(6);
        lngInput.value = Number(lng).toFixed(6);
        setMarker(lat, lng);
    }

    function clearPolygon() {
        if (polygonLayer) {
            map.removeLayer(polygonLayer);
            polygonLayer = null;
        }
    }

    function drawPolygon(geojson) {
        clearPolygon();
        if (!geojson) return;
        polygonLayer = L.geoJSON(geojson, {
            style: { color: '#2563eb', weight: 2, fillOpacity: 0.1 },
        }).addTo(map);
    }

    // Klik pada peta → update marker + koordinat + kosongkan geometry.
    map.on('click', (e) => {
        setCoords(e.latlng.lat, e.latlng.lng);
        clearPolygon();
        geojsonInput.value = '';
    });

    // Edit manual latitude/longitude → sinkronkan marker.
    [latInput, lngInput].forEach((input) => {
        input.addEventListener('change', () => {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (isNaN(lat) || isNaN(lng)) return;
            setMarker(lat, lng);
            map.setView([lat, lng]);
            clearPolygon();
            geojsonInput.value = '';
        });
    });

    // Gambar geometry lama saat edit.
    const existingGeojson = parseJson(geojsonInput.value);
    if (existingGeojson && existingGeojson.type) {
        drawPolygon(existingGeojson);
        map.fitBounds(polygonLayer.getBounds(), { padding: [30, 30] });
    }

    /* ---------------- Autocomplete GeoServer ---------------- */

    function renderResults(items) {
        resultsEl.innerHTML = '';

        if (!items.length) {
            resultsEl.innerHTML =
                '<div class="px-3 py-2 text-sm text-gray-500">Tidak ditemukan.</div>';
            resultsEl.classList.remove('hidden');
            return;
        }

        items.forEach((item) => {
            const div = document.createElement('div');
            div.className =
                'px-3 py-2 text-sm text-gray-800 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
            div.textContent = item.name;
            div.addEventListener('click', () => selectLocation(item));
            resultsEl.appendChild(div);
        });

        resultsEl.classList.remove('hidden');
    }

    function hideResults() {
        resultsEl.classList.add('hidden');
    }

    const runSearch = debounce(async (q) => {
        if (q.length < 2) {
            hideResults();
            return;
        }

        try {
            const response = await fetch('/api/locations/search?q=' + encodeURIComponent(q));
            if (!response.ok) throw new Error('GeoServer search failed');
            renderResults(await response.json());
        } catch (err) {
            resultsEl.innerHTML =
                '<div class="px-3 py-2 text-sm text-red-600">Layanan pencarian lokasi tidak tersedia.</div>';
            resultsEl.classList.remove('hidden');
        }
    }, 300);

    async function selectLocation(item) {
        searchInput.value = item.name;
        locationInput.value = item.name;
        setCoords(item.latitude, item.longitude);
        hideResults();
        map.flyTo([item.latitude, item.longitude], 12);
        clearPolygon();
        geojsonInput.value = '';

        try {
            const response = await fetch('/api/locations/' + encodeURIComponent(item.id));
            if (!response.ok) return;
            const detail = await response.json();
            if (detail.geometry && detail.geometry.type) {
                geojsonInput.value = JSON.stringify(detail.geometry);
                drawPolygon(detail.geometry);
                map.fitBounds(polygonLayer.getBounds(), { padding: [30, 30] });
            }
        } catch (err) {
            // Geometry opsional — koordinat tetap terisi.
        }
    }

    searchInput.addEventListener('input', (e) => runSearch(e.target.value.trim()));

    searchInput.addEventListener('focus', () => {
        const q = searchInput.value.trim();
        if (q.length >= 2) runSearch(q);
    });

    document.addEventListener('click', (e) => {
        if (e.target !== searchInput && !resultsEl.contains(e.target)) {
            hideResults();
        }
    });

    /* ---------------- Date picker ---------------- */

    const dateInput = document.getElementById('event_date');
    if (dateInput) {
        flatpickr(dateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true,
        });
    }

    /* ---------------- Preview gambar ---------------- */

    form.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const preview = document.getElementById(input.dataset.previewTarget);
            const file = input.files[0];
            if (!preview || !file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });
    });

    /* ---------------- Preview video ---------------- */

    form.querySelectorAll('[data-video-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const preview = document.getElementById(input.dataset.videoPreview);
            const file = input.files[0];
            if (!preview || !file) return;

            preview.innerHTML = '';
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            preview.load();
        });
    });

    /* ---------------- Orientation + aspect ratio preview ---------------- */

    function applyOrientation() {
        const selected = form.querySelector('input[name="orientation"]:checked');
        if (!selected) return;
        const ratio = selected.value === 'landscape' ? '16 / 9' : '4 / 3';
        form.querySelectorAll('[data-orientation-preview]').forEach((img) => {
            img.style.aspectRatio = ratio;
        });
    }

    form.querySelectorAll('input[name="orientation"]').forEach((radio) => {
        radio.addEventListener('change', applyOrientation);
    });

    applyOrientation();
}

document.addEventListener('DOMContentLoaded', () => {
    initDetailMap();
    initForm();
});
