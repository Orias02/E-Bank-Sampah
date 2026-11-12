/**
 * Interaksi halaman Login, Pendaftaran, dan menu profil dashboard nasabah.
 * Dimuat dari resources/js/app.js lewat: import './auth';
 */

// Interaksi form pendaftaran (langkah Data Diri) ikut dimuat dari sini.
import './wizard';

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

/* Tombol mata: tampilkan / sembunyikan kata sandi */
function initPasswordToggles() {
    $$('[data-password-toggle]').forEach((btn) => {
        const input = document.getElementById(btn.dataset.passwordToggle);
        if (!input) return;

        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.dataset.visible = String(show);
            btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            input.focus({ preventScroll: true });
        });
    });
}

/* Tombol kirim menampilkan spinner agar tidak terkirim dua kali */
function initFormLoading() {
    $$('form[data-auth-form]').forEach((form) => {
        const btn = $('[type="submit"]', form);
        if (!btn) return;

        form.addEventListener('submit', () => {
            btn.dataset.loading = 'true';
            btn.disabled = true;
        });
    });

    // Kembali lewat tombol "back" browser: aktifkan lagi tombolnya.
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        $$('form[data-auth-form] [type="submit"]').forEach((btn) => {
            btn.dataset.loading = 'false';
            btn.disabled = false;
        });
    });
}

/* Petunjuk langsung: kata sandi dan konfirmasinya sudah sama atau belum */
function initPasswordMatch() {
    const password = $('#password');
    const confirm = $('#password_confirmation');
    const hint = $('#confirm-hint');
    if (!password || !confirm || !hint) return;

    const check = () => {
        if (!confirm.value) {
            hint.hidden = true;
            return;
        }
        const ok = password.value === confirm.value;
        hint.hidden = false;
        hint.dataset.ok = String(ok);
        hint.textContent = ok ? 'Kata sandi cocok.' : 'Kata sandi belum sama.';
    };

    password.addEventListener('input', check);
    confirm.addEventListener('input', check);
}

/* Menu profil (<details data-menu>): tutup saat klik di luar atau menekan Esc */
function initMenus() {
    const closeAll = (except = null) =>
        $$('details[data-menu][open]').forEach((menu) => {
            if (menu !== except) menu.removeAttribute('open');
        });

    document.addEventListener('click', (e) => closeAll(e.target.closest('details[data-menu]')));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll();
    });
}

/* Profil: tombol "Ubah Foto" membuka pemilih berkas, lalu foto langsung diunggah */
function initProfilePhoto() {
    const form = $('[data-photo-form]');
    if (!form) return;

    const input = $('[data-photo-input]', form);
    const trigger = $('[data-photo-trigger]', form);
    const errorBox = $('[data-photo-error]');
    const errorText = $('[data-photo-error-text]');
    const MAX_BYTES = 2 * 1024 * 1024;

    trigger.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        let message = null;
        if (!/^image\/(jpeg|png|webp)$/.test(file.type) && !/\.(jpe?g|png|webp)$/i.test(file.name)) {
            message = 'Format foto harus JPG, PNG, atau WebP.';
        } else if (file.size > MAX_BYTES) {
            message = 'Ukuran foto melebihi 2 MB. Pilih foto yang lebih kecil.';
        }

        if (message) {
            input.value = ''; // berkas yang ditolak tidak ikut terkirim
            errorText.textContent = message;
            errorBox.hidden = false;
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        errorBox.hidden = true;
        trigger.disabled = true;
        trigger.textContent = 'Mengunggah…';
        form.submit();
    });

    // Kembali lewat tombol "back" browser: kembalikan tombolnya.
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        trigger.disabled = false;
        trigger.textContent = 'Ubah Foto';
        input.value = '';
    });
}

/* Jika server mengembalikan error, fokus ke kolom pertama yang bermasalah */
function focusFirstInvalid() {
    $('[aria-invalid="true"]')?.focus();
}

function init() {
    initPasswordToggles();
    initFormLoading();
    initPasswordMatch();
    initMenus();
    initProfilePhoto();
    focusFirstInvalid();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}