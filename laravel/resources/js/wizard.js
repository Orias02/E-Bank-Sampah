/**
 * Interaksi form wizard pendaftaran nasabah: langkah Data Diri dan Alamat.
 * Dimuat otomatis dari resources/js/auth.js (tidak perlu diimpor di app.js).
 * Form dipilih lewat atribut data-wizard-form="data-diri" atau "alamat".
 *
 * Validasi di browser memakai pesan dan elemen yang sama dengan error dari server
 * (data-field, data-hint, data-error), jadi tampilannya selalu konsisten.
 */

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

const MAX_FILE_BYTES = 5 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'application/pdf'];
const ALLOWED_EXTENSION = /\.(jpe?g|png|pdf)$/i;

const number = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });

function formatBytes(bytes) {
    if (bytes >= 1024 * 1024) return `${number.format(bytes / 1024 / 1024)} MB`;
    return `${number.format(Math.max(1, Math.round(bytes / 1024)))} KB`;
}

/* ------------------------------------------------------------------
   Aturan validasi per kolom. Mengembalikan pesan error, atau undefined jika valid.
------------------------------------------------------------------- */
const validators = {
    nama_lengkap(value) {
        const v = value.trim();
        if (!v) return 'Nama lengkap wajib diisi.';
        if (v.length < 3) return 'Nama lengkap minimal 3 karakter.';
        if (!/^[\p{L}\s.'-]+$/u.test(v)) return 'Nama hanya boleh berisi huruf.';
    },
    nik(value) {
        if (!value) return 'NIK wajib diisi.';
        if (!/^\d{16}$/.test(value)) return 'NIK harus terdiri dari 16 digit angka.';
    },
    tanggal_lahir(value) {
        if (!value) return 'Tanggal lahir wajib diisi.';
        if (new Date(value) > new Date()) return 'Tanggal lahir tidak boleh di masa depan.';
    },
    jenis_kelamin(value) {
        if (!value) return 'Pilih jenis kelamin.';
    },
    whatsapp(value) {
        const digits = value.replace(/\D/g, '');
        if (!digits) return 'Nomor WhatsApp wajib diisi.';
        if (digits.length < 9 || digits.length > 13) return 'Masukkan 9–13 digit nomor setelah +62.';
    },
    email(value) {
        const v = value.trim();
        if (!v) return 'Email wajib diisi.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Format email belum valid. Contoh: nama@gmail.com.';
    },
};

const alamatValidators = {
    kota: (v) => (v ? undefined : 'Pilih kota.'),
    kecamatan: (v) => (v ? undefined : 'Pilih kecamatan.'),
    kelurahan: (v) => (v ? undefined : 'Pilih kelurahan.'),
    rt: (v) => rtrw('RT', v),
    rw: (v) => rtrw('RW', v),
    alamat_lengkap(value) {
        const v = value.trim();
        if (!v) return 'Alamat lengkap wajib diisi.';
        if (v.length < 10) return 'Alamat terlalu singkat. Tulis nama jalan dan nomor rumah.';
    },
    bank_sampah_id: (v) => (v ? undefined : 'Pilih bank sampah.'),
};

function rtrw(label, value) {
    if (!value) return `${label} wajib diisi.`;
    if (!/^\d{1,3}$/.test(value)) return `${label} berupa angka, maksimal 3 digit.`;
    if (/^0+$/.test(value)) return `${label} tidak boleh 0.`;
}

/* ------------------------------------------------------------------
   Kerangka umum wizard: tampil/hapus error, ringkasan error, validasi langsung,
   penahanan kirim, dan fokus ke kolom bermasalah. Dipakai semua langkah.

   - readers:      { namaKolom: () => nilaiSaatIni }
   - validators:   { namaKolom: (nilai) => pesanError | undefined }
   - extraChecks:  [{ name, validate: () => pesanError | null }] untuk kolom khusus (mis. berkas)
------------------------------------------------------------------- */
function createWizard(form, { readers, validators, extraChecks = [] }) {
    const field = (name) => $(`[data-field="${name}"]`, form);
    const summary = $('[data-error-summary]', form);
    const summaryTitle = $('[data-error-summary-title]', form);
    const submitButton = $('[type="submit"]', form);

    const setError = (name, message) => {
        const wrap = field(name);
        if (!wrap) return;
        const invalid = Boolean(message);
        wrap.dataset.invalid = String(invalid);

        const error = $('[data-error]', wrap);
        const hint = $('[data-hint]', wrap);
        if (error) {
            error.textContent = message || '';
            error.hidden = !invalid;
        }
        if (hint) hint.hidden = invalid;

        $$('[data-control]', wrap).forEach((control) => {
            if (control.type === 'radio') return;
            control.setAttribute('aria-invalid', String(invalid));
        });
    };

    const refreshSummary = () => {
        const count = $$('[data-field][data-invalid="true"]', form).length;
        if (!summary) return;
        summary.hidden = count === 0;
        if (count && summaryTitle) summaryTitle.textContent = `Periksa ${count} bagian yang ditandai.`;
    };

    const validateField = (name) => {
        const message = validators[name]?.(readers[name]());
        setError(name, message);
        return message;
    };

    /* Validasi langsung: cek saat meninggalkan kolom, perbarui saat mengetik */
    Object.keys(readers).forEach((name) => {
        const wrap = field(name);
        if (!wrap) return;

        wrap.addEventListener('focusout', (e) => {
            if (wrap.contains(e.relatedTarget)) return;
            if (readers[name]() !== '') {
                validateField(name);
                refreshSummary();
            }
        });
        const revalidate = () => {
            if (wrap.dataset.invalid === 'true') {
                validateField(name);
                refreshSummary();
            }
        };
        wrap.addEventListener('input', revalidate);
        wrap.addEventListener('change', revalidate);
    });

    /* Kirim: tahan jika ada yang belum valid */
    form.addEventListener('submit', (e) => {
        let firstInvalid = null;

        Object.keys(readers).forEach((name) => {
            if (validateField(name)) firstInvalid ??= name;
        });
        extraChecks.forEach(({ name, validate }) => {
            const message = validate();
            setError(name, message);
            if (message) firstInvalid ??= name;
        });

        if (firstInvalid) {
            e.preventDefault();
            refreshSummary();
            summary?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            $('[data-control]', field(firstInvalid))?.focus({ preventScroll: true });
            return;
        }

        if (submitButton) {
            submitButton.dataset.loading = 'true';
            submitButton.disabled = true;
        }
    });

    // Kembali lewat tombol "back" browser: aktifkan lagi tombol kirim.
    window.addEventListener('pageshow', (e) => {
        if (e.persisted && submitButton) {
            submitButton.dataset.loading = 'false';
            submitButton.disabled = false;
        }
    });

    // Jika server mengembalikan error, fokus ke kolom pertama yang bermasalah.
    if (summary && !summary.hidden) {
        $('[data-field][data-invalid="true"] [data-control]', form)?.focus();
    }

    return { field, setError, refreshSummary, validateField };
}

/* ------------------------------------------------------------------
   Langkah Data Diri
------------------------------------------------------------------- */
function initDataDiriForm(form) {
    const readers = {
        nama_lengkap: () => $('#nama_lengkap').value,
        nik: () => $('#nik').value,
        tanggal_lahir: () => $('#tanggal_lahir').value,
        jenis_kelamin: () => $('input[name="jenis_kelamin"]:checked', form)?.value ?? '',
        whatsapp: () => $('#whatsapp').value,
        email: () => $('#email').value,
    };

    // Dideklarasikan dulu, diisi setelah createWizard (butuh setError / refreshSummary).
    let validateFile = () => null;
    const wizard = createWizard(form, {
        readers,
        validators,
        extraChecks: [{ name: 'foto_ktp', validate: () => validateFile() }],
    });
    const { field, setError, refreshSummary } = wizard;

    /* ----- NIK: hanya angka, maksimal 16 digit ----- */
    const nik = $('#nik');
    nik?.addEventListener('input', () => {
        nik.value = nik.value.replace(/\D/g, '').slice(0, 16);
    });

    /* ----- WhatsApp: angka saja, buang 0 / 62 di depan, tampil berkelompok (812 3456 7890) ----- */
    const whatsapp = $('#whatsapp');
    const formatWhatsapp = () => {
        let digits = whatsapp.value.replace(/\D/g, '').replace(/^0+/, '');
        if (digits.startsWith('62')) digits = digits.slice(2).replace(/^0+/, '');
        digits = digits.slice(0, 13);
        whatsapp.value = [digits.slice(0, 3), digits.slice(3, 7), digits.slice(7)].filter(Boolean).join(' ');
    };
    if (whatsapp) {
        formatWhatsapp();
        whatsapp.addEventListener('input', formatWhatsapp);
    }

    /* ----- Tanggal lahir: tampil "12 Mei 1998", pemilih tanggal bawaan browser ----- */
    const dateInput = $('#tanggal_lahir');
    const dateDisplay = $('[data-date-display]', form);
    if (dateInput && dateDisplay) {
        const dateFormat = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        const today = new Date();
        dateInput.max = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, '0'),
            String(today.getDate()).padStart(2, '0'),
        ].join('-');

        const renderDate = () => {
            if (!dateInput.value) {
                dateDisplay.textContent = dateDisplay.dataset.placeholder;
                dateDisplay.dataset.empty = 'true';
                return;
            }
            const [y, m, d] = dateInput.value.split('-').map(Number);
            dateDisplay.textContent = dateFormat.format(new Date(y, m - 1, d));
            dateDisplay.dataset.empty = 'false';
        };
        renderDate();
        dateInput.addEventListener('input', renderDate);
        dateInput.addEventListener('change', renderDate);
        dateInput.addEventListener('click', () => {
            try {
                dateInput.showPicker();
            } catch {
                /* browser lama: kolom tanggal tetap bisa diisi manual */
            }
        });
    }

    /* ----- Foto KTP: pilih / tarik berkas, cek tipe dan ukuran ----- */
    const dropzone = field('foto_ktp');
    const fileInput = $('#foto_ktp');
    const dropArea = $('[data-drop-area]', form);
    const fileItem = $('[data-file-item]', form);
    const fileName = $('[data-file-name]', form);
    const fileMeta = $('[data-file-meta]', form);
    const removedInput = $('[data-ktp-removed]', form);
    const hadStored = dropzone?.dataset.stored === 'true';

    const showFile = (name, bytes, state, status) => {
        fileItem.hidden = false;
        fileItem.dataset.state = state;
        fileName.textContent = name;
        fileMeta.textContent = `${formatBytes(bytes)} • ${status}`;
    };

    const clearFile = () => {
        fileInput.value = '';
        fileItem.hidden = true;
        dropzone.dataset.stored = 'false';
        if (hadStored) removedInput.value = '1';
        setError('foto_ktp', null);
        refreshSummary();
    };

    const handleFile = (file) => {
        if (!file) return;

        let message = null;
        if (!ALLOWED_TYPES.includes(file.type) && !ALLOWED_EXTENSION.test(file.name)) {
            message = 'Format berkas harus JPG, PNG, atau PDF.';
        } else if (file.size > MAX_FILE_BYTES) {
            message = 'Ukuran berkas melebihi 5 MB. Pilih foto yang lebih kecil.';
        }

        if (message) {
            fileInput.value = ''; // berkas yang ditolak tidak ikut terkirim
            showFile(file.name, file.size, 'error', 'Gagal diunggah');
            setError('foto_ktp', message);
        } else {
            dropzone.dataset.stored = 'false'; // berkas baru menggantikan yang tersimpan
            removedInput.value = '0';
            showFile(file.name, file.size, 'ok', 'Berhasil diunggah');
            setError('foto_ktp', null);
        }
        refreshSummary();
    };

    validateFile = () => {
        if (fileInput.files.length || dropzone.dataset.stored === 'true') return null;
        // Berkas sebelumnya ditolak: pertahankan pesan spesifiknya.
        if (!fileItem.hidden && fileItem.dataset.state === 'error') return $('[data-error]', dropzone).textContent;
        return 'Foto KTP wajib diunggah.';
    };

    if (dropzone && fileInput && dropArea && fileItem) {
        fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));
        $('[data-file-remove]', form)?.addEventListener('click', clearFile);

        ['dragenter', 'dragover'].forEach((type) =>
            dropArea.addEventListener(type, (e) => {
                e.preventDefault();
                dropArea.dataset.dragging = 'true';
            }),
        );
        ['dragleave', 'drop'].forEach((type) =>
            dropArea.addEventListener(type, () => {
                dropArea.dataset.dragging = 'false';
            }),
        );
        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            const file = e.dataTransfer?.files?.[0];
            if (!file) return;
            try {
                const transfer = new DataTransfer();
                transfer.items.add(file);
                fileInput.files = transfer.files;
            } catch {
                /* browser lama: pengguna memakai tombol "Pilih berkas" */
            }
            handleFile(file);
        });
    }

}

/* ------------------------------------------------------------------
   Langkah Alamat: dropdown bertingkat, kartu bank sampah, RT/RW
------------------------------------------------------------------- */
function initAlamatForm(form) {
    const wilayah = JSON.parse($('#wilayah-data')?.textContent || '{}');

    const kota = $('#kota');
    const kecamatan = $('#kecamatan');
    const kelurahan = $('#kelurahan');

    const readers = {
        kota: () => kota.value,
        kecamatan: () => kecamatan.value,
        kelurahan: () => kelurahan.value,
        rt: () => $('#rt').value,
        rw: () => $('#rw').value,
        alamat_lengkap: () => $('#alamat_lengkap').value,
        bank_sampah_id: () => $('input[name="bank_sampah_id"]:checked', form)?.value ?? '',
    };

    const { setError } = createWizard(form, { readers, validators: alamatValidators });

    /* ----- Isi ulang satu dropdown ----- */
    const fill = (select, items, placeholder, emptyPlaceholder) => {
        select.replaceChildren(new Option(items.length ? placeholder : emptyPlaceholder, ''), ...items.map((item) => new Option(item, item)));
        select.value = '';
        select.disabled = items.length === 0;
    };

    /* ----- Kartu bank sampah: tampilkan yang melayani kelurahan terpilih ----- */
    const banks = $$('[data-bank]', form);
    const bankPlaceholder = $('[data-bank-placeholder]', form);
    const bankEmpty = $('[data-bank-empty]', form);

    const updateBanks = () => {
        const selected = kelurahan.value;
        let visible = 0;

        banks.forEach((card) => {
            const serves = card.dataset.kelurahan === '' || card.dataset.kelurahan === selected;
            const show = selected !== '' && serves;
            card.hidden = !show;
            if (show) visible += 1;
            if (!show) $('input', card).checked = false; // pilihan yang tidak tersedia ikut dibatalkan
        });

        bankPlaceholder.hidden = selected !== '';
        bankEmpty.hidden = selected === '' || visible > 0;
        setError('bank_sampah_id', null);
    };

    kota.addEventListener('change', () => {
        fill(kecamatan, Object.keys(wilayah[kota.value] ?? {}), 'Pilih kecamatan', 'Pilih kota dulu');
        fill(kelurahan, [], '', 'Pilih kecamatan dulu');
        updateBanks();
    });

    kecamatan.addEventListener('change', () => {
        fill(kelurahan, wilayah[kota.value]?.[kecamatan.value] ?? [], 'Pilih kelurahan', 'Pilih kecamatan dulu');
        updateBanks();
    });

    kelurahan.addEventListener('change', updateBanks);

    /* ----- RT / RW: angka saja (maks. 3 digit), dilengkapi nol di depan saat selesai diisi ----- */
    $$('[data-rtrw]', form).forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 3);
        });
        input.addEventListener('blur', () => {
            if (input.value !== '' && !/^0+$/.test(input.value)) input.value = input.value.padStart(3, '0');
        });
    });
}

function init() {
    const form = $('[data-wizard-form]');
    if (!form) return;

    const type = form.dataset.wizardForm;
    if (type === 'data-diri') initDataDiriForm(form);
    if (type === 'alamat') initAlamatForm(form);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}