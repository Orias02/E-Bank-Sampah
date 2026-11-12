/**
 * Interaksi form pendaftaran nasabah (langkah Data Diri dan Alamat), form Profil, Setor Sampah, Tarik Saldo,
 * dan filter Katalog Harga Sampah.
 * Dimuat otomatis dari resources/js/auth.js (tidak perlu diimpor di app.js).
 * Form dipilih lewat atribut data-wizard-form="data-diri", "alamat", "profil", "setor", "tarik",
 * serta form admin "timbang" (hasil timbangan) dan "harga" (jenis & harga sampah).
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
    // Halaman dengan beberapa tombol kirim (mis. Profil) menandai tombol utamanya dengan data-submit.
    const submitButton = $('[data-submit]', form) ?? $('[type="submit"]', form);

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
            if (control.type === 'radio' || control.type === 'checkbox') return;
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
   Kolom tanggal: tampil "12 Mei 1998" (bukan format browser), pemilih tanggal bawaan browser.
   Input type="date" transparan di atas elemen tampilan.
------------------------------------------------------------------- */
function bindDateDisplay(input, display) {
    const dateFormat = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

    const render = () => {
        if (!input.value) {
            display.textContent = display.dataset.placeholder;
            display.dataset.empty = 'true';
            return;
        }
        const [y, m, d] = input.value.split('-').map(Number);
        display.textContent = dateFormat.format(new Date(y, m - 1, d));
        display.dataset.empty = 'false';
    };

    render();
    input.addEventListener('input', render);
    input.addEventListener('change', render);
    input.addEventListener('click', () => {
        try {
            input.showPicker();
        } catch {
            /* browser lama: kolom tanggal tetap bisa diisi manual */
        }
    });
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
        const today = new Date();
        dateInput.max = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, '0'),
            String(today.getDate()).padStart(2, '0'),
        ].join('-');
        bindDateDisplay(dateInput, dateDisplay);
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

/* ------------------------------------------------------------------
   Form Profil nasabah
------------------------------------------------------------------- */
const normalizeWa = (value) => {
    let digits = value.replace(/\D/g, '').replace(/^0+/, '');
    if (digits.startsWith('62')) digits = digits.slice(2).replace(/^0+/, '');
    return digits;
};

function initProfilForm(form) {
    const el = (id) => document.getElementById(id);
    const whatsapp = el('whatsapp');
    const metode = el('metode_pencairan');
    const rekening = el('nomor_rekening');
    const password = el('password');
    const confirm = el('password_confirmation');

    const readers = {
        whatsapp: () => whatsapp.value,
        email: () => el('email').value,
        alamat_lengkap: () => el('alamat_lengkap').value,
        metode_pencairan: () => metode.value,
        nomor_rekening: () => rekening.value,
        password: () => password.value,
        password_confirmation: () => confirm.value,
    };

    const profilValidators = {
        email: validators.email,
        whatsapp(value) {
            const digits = normalizeWa(value);
            if (!digits) return 'Nomor WhatsApp wajib diisi.';
            if (digits.length < 9 || digits.length > 13) return 'Masukkan nomor WhatsApp 9–13 digit (tanpa +62).';
        },
        alamat_lengkap(value) {
            const v = value.trim();
            if (!v) return 'Alamat penjemputan wajib diisi.';
            if (v.length < 10) return 'Alamat terlalu singkat. Tulis nama jalan dan nomor rumah.';
        },
        // Metode dan nomor rekening diisi berpasangan (boleh dua-duanya kosong).
        metode_pencairan(value) {
            if (!value && rekening.value.trim()) return 'Pilih metode pencairan.';
        },
        nomor_rekening(value) {
            const digits = value.replace(/\D/g, '');
            if (!digits) return metode.value ? 'Nomor rekening wajib diisi.' : undefined;
            if (!/^\d{8,20}$/.test(digits)) return 'Nomor rekening 8–20 digit angka.';
        },
        password(value) {
            if (value && value.length < 8) return 'Kata sandi minimal 8 karakter.';
        },
        password_confirmation(value) {
            if (password.value && value !== password.value) return 'Konfirmasi kata sandi tidak sama.';
        },
    };

    const { field, validateField, refreshSummary } = createWizard(form, { readers, validators: profilValidators });

    // Periksa ulang kolom pasangan jika sebelumnya sudah ditandai salah.
    const recheck = (name) => {
        if (field(name)?.dataset.invalid === 'true') {
            validateField(name);
            refreshSummary();
        }
    };
    metode.addEventListener('change', () => recheck('nomor_rekening'));
    rekening.addEventListener('input', () => recheck('metode_pencairan'));
    password.addEventListener('input', () => {
        if (confirm.value) {
            validateField('password_confirmation');
            refreshSummary();
        }
    });

    /* WhatsApp: angka saja, tampil berkelompok (0812 3456 7890) */
    const formatWhatsapp = () => {
        const digits = whatsapp.value.replace(/\D/g, '').slice(0, 15);
        whatsapp.value = [digits.slice(0, 4), digits.slice(4, 8), digits.slice(8)].filter(Boolean).join(' ');
    };
    whatsapp.addEventListener('input', formatWhatsapp);

    /* Nomor rekening: angka saja */
    rekening.addEventListener('input', () => {
        rekening.value = rekening.value.replace(/\D/g, '').slice(0, 20);
    });
}

/* ------------------------------------------------------------------
   Form Setor Sampah (pengajuan setoran)
------------------------------------------------------------------- */
function initSetorForm(form) {
    const MIN_JEMPUT = 10;
    const MAX_BERAT = 500;

    const tanggal = $('#tanggal_rencana');
    const rows = $$('[data-berat-row]', form);
    const checkboxes = $$('input[name="jenis_sampah[]"]', form);
    const radios = $$('input[name="metode"]', form);
    const totalBox = $('[data-total-box]', form);
    const totalValue = $('[data-total-value]', form);
    const jemputNote = $('[data-jemput-note]', form);
    const beratEmpty = $('[data-berat-empty]', form);

    const metode = () => $('input[name="metode"]:checked', form)?.value ?? '';
    const parseBerat = (raw) => parseFloat(String(raw).replace(',', '.'));
    const beratFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
    const formatBerat = (n) => beratFormat.format(Math.round(n * 100) / 100);

    // Saat pengguna masih mengisi (keluar dari satu kolom berat), jangan langsung menegur kolom lain
    // yang belum diisi atau total yang belum cukup; pemeriksaan penuh baru dilakukan saat kirim,
    // atau setelah kolom sudah ditandai salah.
    let submitting = false;
    form.addEventListener('submit', () => {
        submitting = true;
        setTimeout(() => (submitting = false), 0);
    });

    // Baris berat milik kategori yang dicentang: [{ nama, input }]
    const checkedRows = () =>
        checkboxes
            .map((box, i) => ({ box, row: rows[i] }))
            .filter(({ box }) => box.checked)
            .map(({ row }) => ({ nama: row.dataset.nama, input: $('[data-berat]', row) }));

    const totalBerat = () =>
        checkedRows().reduce((sum, { input }) => sum + (parseBerat(input.value) > 0 ? parseBerat(input.value) : 0), 0);

    const readers = {
        metode,
        jenis_sampah: () => checkboxes.filter((box) => box.checked).map((box) => box.value).join(','),
        berat: () => checkedRows().map(({ input }) => input.value.trim()).join('|'),
        tanggal_rencana: () => tanggal.value,
        catatan: () => $('#catatan').value,
    };

    const setorValidators = {
        metode: (v) => (v ? undefined : 'Pilih metode penyetoran.'),
        jenis_sampah: (v) => (v ? undefined : 'Pilih minimal satu kategori sampah.'),
        berat() {
            // Kategori belum dipilih: pesannya sudah ditangani kolom jenis_sampah.
            const strict = submitting || field('berat')?.dataset.invalid === 'true';

            for (const { nama, input } of checkedRows()) {
                const raw = input.value.trim();
                if (raw === '' && !strict) continue;
                const n = parseBerat(raw);
                if (!(n > 0)) return `Isi perkiraan berat ${nama} (lebih dari 0 kg).`;
                if (n > MAX_BERAT) return `Perkiraan berat ${nama} maksimal ${MAX_BERAT} kg.`;
            }
            if (!strict) return undefined;

            const total = totalBerat();
            if (metode() === 'jemput' && checkedRows().length && total < MIN_JEMPUT) {
                return `Penjemputan minimal total berat ${MIN_JEMPUT} kg. Total perkiraan Anda baru ${formatBerat(total)} kg.`;
            }
        },
        tanggal_rencana(value) {
            if (!value) return 'Pilih tanggal setor.';
            if (tanggal.min && value < tanggal.min) return 'Tanggal setor tidak boleh sebelum hari ini.';
            if (tanggal.max && value > tanggal.max) return 'Tanggal setor terlalu jauh (maksimal 60 hari dari sekarang).';
        },
    };

    const wizard = createWizard(form, { readers, validators: setorValidators });
    const { validateField, refreshSummary } = wizard;
    const field = wizard.field;

    bindDateDisplay(tanggal, $('[data-date-display]', form));

    /* ----- Tampilan: baris berat, total, dan keterangan lokasi sesuai pilihan ----- */
    const infos = $$('[data-metode-info]', form);

    const render = () => {
        const chosen = metode();
        const total = totalBerat();
        const any = checkedRows().length > 0;

        checkboxes.forEach((box, i) => {
            rows[i].hidden = !box.checked;
        });
        beratEmpty.hidden = any;
        totalBox.hidden = !any;
        totalValue.textContent = `${formatBerat(total)} kg`;

        jemputNote.hidden = chosen !== 'jemput';
        jemputNote.dataset.ok = String(total >= MIN_JEMPUT);

        infos.forEach((info) => {
            info.hidden = info.dataset.metodeInfo !== chosen;
        });
    };

    // Periksa ulang kolom berat jika sebelumnya sudah ditandai salah (mis. setelah berat atau metode berubah).
    const recheckBerat = () => {
        if (field('berat')?.dataset.invalid === 'true') {
            validateField('berat');
            refreshSummary();
        }
    };

    radios.forEach((radio) =>
        radio.addEventListener('change', () => {
            render();
            recheckBerat();
        }),
    );
    checkboxes.forEach((box) =>
        box.addEventListener('change', () => {
            render();
            recheckBerat();
        }),
    );

    /* Berat: angka dengan satu koma/titik, maksimal 3 digit dan 2 desimal */
    $$('[data-berat]', form).forEach((input) => {
        input.addEventListener('input', () => {
            let value = input.value.replace(/[^\d.,]/g, '');
            const sep = value.search(/[.,]/);
            if (sep === -1) {
                value = value.slice(0, 3);
            } else {
                value = value.slice(0, Math.min(sep, 3)) + value[sep] + value.slice(sep + 1).replace(/[.,]/g, '').slice(0, 2);
            }
            input.value = value;
            render();
        });
    });

    render();
}

/* ------------------------------------------------------------------
   Form Tarik Saldo
------------------------------------------------------------------- */

// Ubah isi kolom lewat fungsi format(digit) tanpa membuat kursor melompat ke akhir.
function formatKeepCaret(input, format) {
    const caret = input.selectionStart ?? input.value.length;
    const digitsBefore = input.value.slice(0, caret).replace(/\D/g, '').length;

    input.value = format(input.value.replace(/\D/g, ''));

    let pos = 0;
    let seen = 0;
    while (pos < input.value.length && seen < digitsBefore) {
        if (/\d/.test(input.value[pos])) seen++;
        pos++;
    }
    if (document.activeElement === input) input.setSelectionRange(pos, pos);
}

function initTarikForm(form) {
    const saldo = Number(form.dataset.saldo) || 0;
    const minTarik = Number(form.dataset.min) || 0;
    const rupiah = new Intl.NumberFormat('id-ID');
    const el = (id) => document.getElementById(id);

    const nominal = el('nominal');
    const tujuan = el('tujuan');
    const nomor = el('nomor_tujuan');
    const nama = el('nama_pemilik');
    const chips = $$('[data-chip]', form);
    const sumNominal = $('[data-sum-nominal]', form);
    const sumSisa = $('[data-sum-sisa]', form);

    const digits = (value) => value.replace(/\D/g, '');
    const jumlah = () => Number(digits(nominal.value)) || 0;

    const readers = {
        nominal: () => digits(nominal.value),
        tujuan: () => tujuan.value,
        nomor_tujuan: () => digits(nomor.value),
        nama_pemilik: () => nama.value,
    };

    const tarikValidators = {
        nominal(value) {
            if (!value) return 'Masukkan nominal penarikan.';
            const n = Number(value);
            if (n < minTarik) return `Minimal penarikan Rp ${rupiah.format(minTarik)}.`;
            if (n > saldo) return `Nominal melebihi saldo Anda (Rp ${rupiah.format(saldo)}).`;
        },
        tujuan: (value) => (value ? undefined : 'Pilih tujuan pencairan.'),
        nomor_tujuan(value) {
            if (!value) return 'Nomor rekening / No. HP wajib diisi.';
            if (!/^\d{8,20}$/.test(value)) return 'Nomor rekening 8–20 digit angka.';
        },
        nama_pemilik(value) {
            const v = value.trim();
            if (!v) return 'Nama pemilik rekening wajib diisi.';
            if (v.length < 3) return 'Nama pemilik minimal 3 karakter.';
            if (!/^[\p{L}\s.'-]+$/u.test(v)) return 'Nama hanya boleh berisi huruf.';
        },
    };

    createWizard(form, { readers, validators: tarikValidators });

    /* Ringkasan: nominal, sisa saldo, dan chip yang sedang dipilih */
    const render = () => {
        const n = jumlah();
        const over = n > saldo;

        sumNominal.textContent = `Rp ${rupiah.format(n)}`;
        sumSisa.dataset.over = String(over);
        sumSisa.textContent = over ? 'Saldo tidak cukup' : `Rp ${rupiah.format(saldo - n)}`;

        chips.forEach((chip) => {
            const nilai = chip.dataset.chip === 'all' ? saldo : Number(chip.dataset.chip);
            chip.dataset.active = String(n > 0 && n === nilai);
        });
    };

    nominal.addEventListener('input', () => {
        formatKeepCaret(nominal, (d) => {
            const bersih = d.replace(/^0+(?=\d)/, '').slice(0, 9);
            return bersih ? rupiah.format(Number(bersih)) : '';
        });
        render();
    });

    chips.forEach((chip) =>
        chip.addEventListener('click', () => {
            const nilai = chip.dataset.chip === 'all' ? saldo : Number(chip.dataset.chip);
            nominal.value = rupiah.format(nilai);
            // Memicu validasi langsung (jika kolom sudah ditandai salah) lewat event yang sama dengan mengetik.
            nominal.dispatchEvent(new Event('input', { bubbles: true }));
            nominal.focus();
        }),
    );

    /* Nomor rekening / HP: angka saja, tampil berkelompok 4 digit */
    nomor.addEventListener('input', () => {
        formatKeepCaret(nomor, (d) => d.slice(0, 20).replace(/(\d{4})(?=\d)/g, '$1 '));
    });

    render();
}

/* ------------------------------------------------------------------
   Katalog Harga Sampah: filter kategori + pencarian (sisi browser)
------------------------------------------------------------------- */
function initKatalog(root) {
    const items = $$('[data-item]', root);
    const filters = $$('[data-filter]', root);
    const search = $('[data-cari]', root);
    const empty = $('[data-kosong]', root);
    const summary = $('[data-jumlah]', root);

    let kategori = '';

    const apply = () => {
        const kata = search.value.trim().toLowerCase();
        let tampil = 0;

        items.forEach((item) => {
            const cocok = (!kategori || item.dataset.kategori === kategori) && (!kata || item.dataset.cari.includes(kata));
            item.hidden = !cocok;
            if (cocok) tampil++;
        });

        empty.hidden = tampil > 0;
        summary.textContent = `Menampilkan ${tampil} dari ${items.length} jenis sampah.`;
    };

    const pilih = (nilai) => {
        kategori = nilai;
        filters.forEach((tombol) => {
            const aktif = tombol.dataset.filter === nilai;
            tombol.dataset.active = String(aktif);
            tombol.setAttribute('aria-pressed', String(aktif));
        });
        apply();
    };

    filters.forEach((tombol) => tombol.addEventListener('click', () => pilih(tombol.dataset.filter)));
    search.addEventListener('input', apply);
    $('[data-reset]', root)?.addEventListener('click', () => {
        search.value = '';
        pilih('');
        search.focus();
    });
}

/* ------------------------------------------------------------------
   Admin: form hasil timbangan (pengajuan dan setoran langsung)
------------------------------------------------------------------- */
function initTimbangForm(form) {
    const MAX_BERAT = 1000;

    const rows = $$('[data-timbang-row]', form).map((row) => ({
        nama: row.dataset.nama,
        harga: Number(row.dataset.harga) || 0,
        input: $('[data-berat]', row),
        subtotal: $('[data-subtotal]', row),
    }));
    const totalKg = $('[data-total-kg]', form);
    const totalNilai = $('[data-total-nilai]', form);
    const hasMetode = Boolean($('[data-field="metode"]', form));

    const parseBerat = (raw) => parseFloat(String(raw).replace(',', '.'));
    const beratFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
    const rupiah = (n) => `Rp ${Math.round(n).toLocaleString('id-ID')}`;
    const filled = () => rows.filter(({ input }) => input.value.trim() !== '');

    // Seperti form Setor: selama masih mengisi, jangan menegur yang belum diisi; pemeriksaan penuh saat kirim.
    let submitting = false;
    form.addEventListener('submit', () => {
        submitting = true;
        setTimeout(() => (submitting = false), 0);
    });

    const readers = {
        berat: () => rows.map(({ input }) => input.value.trim()).join('|'),
    };
    const validators = {
        berat() {
            const strict = submitting || field('berat')?.dataset.invalid === 'true';

            for (const { nama, input } of filled()) {
                const n = parseBerat(input.value);
                if (!(n > 0)) return `Berat ${nama} harus lebih dari 0 kg.`;
                if (n > MAX_BERAT) return `Berat ${nama} maksimal ${MAX_BERAT.toLocaleString('id-ID')} kg.`;
            }
            if (strict && filled().length === 0) return 'Isi berat minimal satu kategori.';
        },
    };

    if (hasMetode) {
        readers.metode = () => $('input[name="metode"]:checked', form)?.value ?? '';
        validators.metode = (v) => (v ? undefined : 'Pilih cara sampah diterima.');
    }

    const wizard = createWizard(form, { readers, validators });
    const field = wizard.field;

    const render = () => {
        let kg = 0;
        let nilai = 0;

        rows.forEach(({ input, harga, subtotal }) => {
            const n = parseBerat(input.value);
            const sub = n > 0 ? Math.round(n * harga) : 0;
            if (n > 0) {
                kg += n;
                nilai += sub;
            }
            subtotal.textContent = n > 0 ? rupiah(sub) : '-';
            subtotal.classList.toggle('text-neutral-400', !(n > 0));
            subtotal.classList.toggle('text-neutral-900', n > 0);
        });

        totalKg.textContent = `${beratFormat.format(Math.round(kg * 100) / 100)} kg`;
        totalNilai.textContent = rupiah(nilai);
    };

    // Berat: angka dengan satu koma/titik, maksimal 4 digit dan 2 desimal
    rows.forEach(({ input }) => {
        input.addEventListener('input', () => {
            let value = input.value.replace(/[^\d.,]/g, '');
            const sep = value.search(/[.,]/);
            if (sep === -1) {
                value = value.slice(0, 4);
            } else {
                value = value.slice(0, Math.min(sep, 4)) + value[sep] + value.slice(sep + 1).replace(/[.,]/g, '').slice(0, 2);
            }
            input.value = value;
            render();
        });
    });

    render();
}

/* ------------------------------------------------------------------
   Admin: form jenis sampah dan harga
------------------------------------------------------------------- */
function initHargaForm(form) {
    const MAX_HARGA = 10_000_000;

    const nama = $('#nama', form);
    const kategori = $('#kategori', form);
    const harga = $('#harga_per_kg', form);
    const deskripsi = $('#deskripsi', form);
    const digits = (v) => v.replace(/\D/g, '');

    const readers = {
        nama: () => nama.value.trim(),
        kategori: () => kategori.value.trim(),
        harga_per_kg: () => digits(harga.value),
        deskripsi: () => deskripsi.value,
        ikon: () => $('input[name="ikon"]:checked', form)?.value ?? '',
    };

    const validators = {
        nama(v) {
            if (!v) return 'Nama jenis sampah wajib diisi.';
            if (v.length > 100) return 'Nama maksimal 100 karakter.';
        },
        kategori(v) {
            if (!v) return 'Kategori wajib diisi.';
            if (v.length > 30) return 'Kategori maksimal 30 karakter.';
        },
        harga_per_kg(v) {
            if (!v) return 'Harga per kg wajib diisi.';
            if (Number(v) < 1) return 'Harga harus lebih dari 0.';
            if (Number(v) > MAX_HARGA) return 'Harga terlalu besar.';
        },
        deskripsi: (v) => (v.length > 500 ? 'Deskripsi maksimal 500 karakter.' : undefined),
        ikon: (v) => (v ? undefined : 'Pilih ikon.'),
    };

    createWizard(form, { readers, validators });

    harga.addEventListener('input', () => {
        formatKeepCaret(harga, (d) => {
            d = d.slice(0, 8);
            return d ? Number(d).toLocaleString('id-ID') : '';
        });
    });
}

function init() {
    const katalog = $('[data-katalog]');
    if (katalog && $('[data-item]', katalog)) initKatalog(katalog);

    const form = $('[data-wizard-form]');
    if (!form) return;

    const type = form.dataset.wizardForm;
    if (type === 'data-diri') initDataDiriForm(form);
    if (type === 'alamat') initAlamatForm(form);
    if (type === 'profil') initProfilForm(form);
    if (type === 'setor') initSetorForm(form);
    if (type === 'tarik') initTarikForm(form);
    if (type === 'timbang') initTimbangForm(form);
    if (type === 'harga') initHargaForm(form);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}