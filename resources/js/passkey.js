/**
 * Masuk dan mendaftarkan perangkat dengan biometrik (WebAuthn / passkey).
 *
 * Sidik jari, wajah, atau PIN perangkat tidak pernah sampai ke server — yang
 * dikirim hanya tanda tangan kriptografis. Sisi server: app/Http/Controllers/WebAuthn.
 */
import Webpass from '@laragear/webpass';

// CSRF tetap dinyalakan di rute WebAuthn; Webpass membacanya dari cookie XSRF.
const webpass = Webpass.create({ findXsrfToken: true, findCsrfToken: true });

window.cmsPasskey = {
    didukung: () => Webpass.isSupported(),

    // Masuk tanpa email: perangkat memilihkan kunci yang tersimpan.
    async masuk() {
        const { success, error } = await webpass.assert('/webauthn/login/options', '/webauthn/login');
        return { success, error };
    },

    // Mendaftarkan perangkat ini untuk akun yang sedang masuk.
    async daftar(nama) {
        const respons = '/webauthn/register?alias=' + encodeURIComponent(nama || '');
        const { success, error } = await webpass.attest('/webauthn/register/options', respons);
        return { success, error };
    },
};
