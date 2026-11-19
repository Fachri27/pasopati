/**
 * Masuk dan mendaftarkan perangkat dengan biometrik (WebAuthn / passkey).
 *
 * Sidik jari, wajah, atau PIN perangkat tidak pernah sampai ke server — yang
 * dikirim hanya tanda tangan kriptografis. Sisi server: app/Http/Controllers/WebAuthn.
 */
import Webpass from '@laragear/webpass';

// Token CSRF disetel sendiri di header. Opsi findCsrfToken milik Webpass
// ternyata tidak mengirim apa pun (permintaan ditolak 419), dan layout masuk
// tidak punya <meta csrf-token>, jadi cookie XSRF-TOKEN yang dipakai: Laravel
// menerimanya lewat header X-XSRF-TOKEN.
const header = () => {
    const meta = document.querySelector('meta[name="csrf-token"]')?.content;
    if (meta) return { 'X-CSRF-TOKEN': meta };

    const cookie = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return cookie ? { 'X-XSRF-TOKEN': decodeURIComponent(cookie[1]) } : {};
};

const ceremony = (path) => ({ path, headers: header() });

const pesanGalat = (error) => {
    if (!error) return '';
    console.error('Biometrik gagal:', error);
    return [error.name, error.message].filter(Boolean).join(': ');
};

window.cmsPasskey = {
    didukung: () => Webpass.isSupported(),

    // Masuk tanpa email: perangkat memilihkan kunci yang tersimpan. Email
    // (opsional) membatasi pilihan pada kunci akun itu.
    async masuk(email) {
        const { success, error } = await Webpass.assert(
            { ...ceremony('/webauthn/login/options'), body: email ? { email } : {} },
            ceremony('/webauthn/login'),
        );
        return { success, error: pesanGalat(error) };
    },

    // Mendaftarkan perangkat ini untuk akun yang sedang masuk.
    async daftar(nama) {
        const { success, error } = await Webpass.attest(
            ceremony('/webauthn/register/options'),
            ceremony('/webauthn/register?alias=' + encodeURIComponent(nama || '')),
        );
        return { success, error: pesanGalat(error) };
    },
};
