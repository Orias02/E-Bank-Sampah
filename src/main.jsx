import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
  ArrowDownLeft, ArrowLeft, ArrowRight, ArrowUpRight, Banknote, Check,
  ChevronLeft, ChevronRight, CircleHelp, Clock3, FileText, Headset, House,
  MapPin, Menu, Plus, Recycle, Search, Sprout, Tags, Users, Wallet
} from 'lucide-react';
import './style.css';

const pages = [
  { label: 'Dashboard', path: '/dashboard', icon: House },
  { label: 'Setor Sampah', path: '/setor', icon: Recycle },
  { label: 'Tarik Saldo', path: '/withdraw', icon: Wallet },
  { label: 'Riwayat Transaksi', path: '/riwayat', icon: Clock3 },
  { label: 'Katalog Harga', path: '/harga', icon: Tags },
  { label: 'Profil', path: '/profil', icon: Users },
];

const adminPages = [
  { label: 'Dashboard Admin', path: '/admin', icon: House },
  { label: 'Data Nasabah', path: '/admin/nasabah', icon: Users },
  { label: 'Manajemen Transaksi', path: '/admin/transaksi', icon: Banknote },
  { label: 'Jenis Sampah', path: '/admin/jenis-sampah', icon: Recycle },
];

const publicLinks = [
  { label: 'Beranda', path: '/' },
  { label: 'Tentang', path: '/tentang' },
  { label: 'Lokasi', path: '/lokasi' },
  { label: 'Helpdesk', path: '/helpdesk' },
];

function navigate(path) {
  window.history.pushState({}, '', path);
  window.dispatchEvent(new PopStateEvent('popstate'));
  window.scrollTo({ top: 0, behavior: 'auto' });
}

function Button({ children, variant = 'primary', className = '', onClick, type = 'button' }) {
  return <button type={type} className={`button button-${variant} ${className}`} onClick={onClick}>{children}</button>;
}

function BrandMarks() {
  return (
    <div className="brand-marks" aria-label="Logo institusi">
      {['LOGO KITA', 'LOGO UB', 'LOGO FILKOM'].map((name, index) => (
        <div className="brand-mark" key={name}>
          <span>PLACEHOLDER</span><strong>{name}</strong>
          {index < 2 && <i />}
        </div>
      ))}
    </div>
  );
}

function Header({ route, onToggleMenu }) {
  const isAuth = route === '/login' || route === '/register' || route === '/daftar';
  const isHome = route === '/';
  const isLoggedIn = !isHome && !isAuth;
  return (
    <header className={`site-header ${isHome ? 'header-home' : ''}`}>
      {!isHome && <button className="mobile-menu" aria-label="Buka menu" onClick={onToggleMenu}><Menu size={21} /></button>}
      <BrandMarks />
      {!isAuth ? (
        <nav className="public-nav" aria-label="Navigasi utama">
          {publicLinks.map(link => <a key={link.path} className={route === link.path ? 'active' : ''} onClick={() => navigate(link.path)}>{link.label}</a>)}
        </nav>
      ) : <div className="header-spacer" />}
      {isLoggedIn ? (
        <button className="account-chip" onClick={() => navigate('/profil')} aria-label="Buka profil">
          <span><strong>Fatih Ahmad Reza El N</strong><small>Nasabah Aktif</small></span><b>FA</b>
        </button>
      ) : (
        <Button variant="light" className="header-action" onClick={() => navigate(isAuth ? (route === '/register' ? '/login' : '/register') : '/login')}>
          {isAuth ? (route === '/register' ? 'Login' : 'Register') : 'Login'}
        </Button>
      )}
    </header>
  );
}

function PromoPanel() {
  return (
    <aside className="promo-panel">
      <div className="promo-brand"><Recycle size={18} /><span>E-BANK SAMPAH FILKOM</span></div>
      <div className="promo-copy">
        <h2>Sampah bernilai.<br />Langkah kecil,<br />dampak berarti.</h2>
        <p>Jadi bagian dari perubahan. Kelola sampah dari rumah dan mulai tabungan Anda bersama kami.</p>
        <ul className="benefit-list">
          <li><span><Wallet size={19} /></span><div><strong>Sampah jadi tabungan</strong><small>Setor sampah terpilah, kumpulkan saldo.</small></div></li>
          <li><span><MapPin size={19} /></span><div><strong>Dekat dengan rumah</strong><small>Terhubung ke bank sampah di lingkungan Anda.</small></div></li>
          <li><span><Sprout size={19} /></span><div><strong>Dampak nyata untuk lingkungan</strong><small>Mulai kebiasaan baik untuk kota lebih bersih.</small></div></li>
        </ul>
      </div>
      <p className="promo-foot">Dari rumah Anda, untuk lingkungan yang lebih bersih dan berkelanjutan.</p>
    </aside>
  );
}

function Shell({ children, route, menuOpen, onToggleMenu }) {
  const currentLabel = [...pages, ...adminPages, ...publicLinks, { label: 'Pendaftaran Nasabah', path: '/daftar' }]
    .find(page => page.path === route)?.label || 'E-Bank Sampah';
  return (
    <div className="app">
      <Header route={route} onToggleMenu={onToggleMenu} />
      <div className="content-wrap">
        <div className="breadcrumb"><a onClick={() => navigate('/')}>Beranda</a><ChevronRight size={14} />{currentLabel}</div>
        <div className={`app-columns ${menuOpen ? 'menu-open' : ''}`}>
          <PromoPanel />
          {menuOpen && <MobileNav route={route} />}
          <main className="content-panel">{children}</main>
        </div>
        <div className="help-line"><CircleHelp size={15} />Butuh bantuan? <a onClick={() => navigate('/helpdesk')}>Hubungi Helpdesk</a></div>
      </div>
      <footer className="site-footer"><span>© 2026 E-Bank Sampah FILKOM</span><a>Kebijakan Privasi</a></footer>
    </div>
  );
}

function MobileNav({ route }) {
  const links = route.startsWith('/admin') ? adminPages : pages;
  return <nav className="mobile-nav">{links.map(({ label, path, icon: Icon }) => (
    <button key={path} className={route === path ? 'selected' : ''} onClick={() => navigate(path)}><Icon size={17} />{label}</button>
  ))}</nav>;
}

function HomePage() {
  return (
    <div className="landing">
      <Header route="/" />
      <main className="landing-hero">
        <div className="landing-copy">
          <h1>E-Bank Sampah<br />FILKOM</h1>
          <p>Kelompok 1</p>
          <Button variant="hero" onClick={() => navigate('/daftar')}>Pendaftaran Nasabah <ArrowUpRight size={18} /></Button>
        </div>
        <div className="landing-bottom">♻ Kelola sampah, ciptakan masa depan yang lebih berkelanjutan.</div>
      </main>
    </div>
  );
}

function PageHeading({ title, description, eyebrow = 'E-BANK SAMPAH FILKOM', actions }) {
  return (
    <div className="page-heading">
      <div><span className="eyebrow">{eyebrow}</span><h1>{title}</h1>{description && <p>{description}</p>}</div>
      {actions && <div className="heading-actions">{actions}</div>}
    </div>
  );
}

function Card({ children, className = '', onClick }) {
  return <section
    className={`surface-card ${className}`}
    onClick={onClick}
    onKeyDown={onClick ? event => {
      if (event.key === 'Enter' || event.key === ' ') onClick(event);
    } : undefined}
    role={onClick ? 'button' : undefined}
    tabIndex={onClick ? 0 : undefined}
  >{children}</section>;
}

function StatusBadge({ children, tone = 'green' }) {
  return <span className={`status-badge badge-${tone}`}>{children}</span>;
}

function StatCard({ icon: Icon, label, value, note, tone = 'green' }) {
  return <Card className="stat-card"><div className={`stat-icon icon-${tone}`}><Icon size={19} /></div><small>{label}</small><strong>{value}</strong>{note && <span>{note}</span>}</Card>;
}

function DataTable({ headers, rows, emptyText = 'Belum ada data.' }) {
  const [query, setQuery] = useState('');
  const filtered = useMemo(() => rows.filter(row => row.some(cell => String(cell).toLowerCase().includes(query.toLowerCase()))), [rows, query]);
  return (
    <Card className="table-card">
      <div className="table-tools"><label className="search-field"><Search size={16} /><input value={query} onChange={event => setQuery(event.target.value)} placeholder="Cari data..." /></label></div>
      <div className="table-scroll"><table><thead><tr>{headers.map(header => <th key={header}>{header}</th>)}</tr></thead>
        <tbody>{filtered.length ? filtered.map((row, rowIndex) => <tr key={`${rowIndex}-${String(row[0])}`}>{row.map((cell, index) => <td key={index}>{cell}</td>)}</tr>) : <tr><td className="empty-row" colSpan={headers.length}>{emptyText}</td></tr>}</tbody>
      </table></div>
      <div className="table-pagination"><span>Menampilkan {filtered.length} dari {rows.length} data</span><div><button aria-label="Sebelumnya"><ChevronLeft size={16} /></button><button className="current-page">1</button><button>2</button><button aria-label="Selanjutnya"><ChevronRight size={16} /></button></div></div>
    </Card>
  );
}

function Dashboard({ admin = false }) {
  if (admin) {
    const rows = [
      ['#WD-8092', 'Fatih Ahmad Reza', 'Tarik Saldo', 'Rp 100.000', '12 Nov 2026', <StatusBadge tone="yellow">Menunggu</StatusBadge>],
      ['#DP-5514', 'Siti Aminah', 'Setor Sampah', 'Plastik PET · 5,2 Kg', '12 Nov 2026', <StatusBadge>Selesai</StatusBadge>],
      ['#PK-3312', 'Budi Santoso', 'Permintaan Jemput', 'Kardus · 15 Kg', '12 Nov 2026', <StatusBadge tone="yellow">Diproses</StatusBadge>],
    ];
    return <>
      <div className="admin-welcome"><div><h2>Selamat Datang di Portal Admin</h2><p>Pantau aktivitas nasabah, kelola harga sampah, dan validasi transaksi hari ini.</p></div><div className="welcome-actions"><Button variant="secondary"><Search size={16} />Scan QR Nasabah</Button><Button><Plus size={17} />Catat Setoran Baru</Button></div></div>
      <div className="stats-grid three"><StatCard icon={Users} label="Total Nasabah" value="1,248" note="User" tone="blue" /><StatCard icon={ScaleIcon} label="Total Sampah Terkumpul" value="8,450" note="Kg" /><StatCard icon={Wallet} label="Total Saldo Nasabah" value="Rp 15.450.000" tone="orange" /></div>
      <div className="section-title"><h2>Antrean Transaksi & Aktivitas Terbaru</h2><a onClick={() => navigate('/admin/transaksi')}>Lihat Semua Data <ChevronRight size={14} /></a></div>
      <DataTable headers={['ID TRANSAKSI', 'NASABAH', 'JENIS AKTIVITAS', 'TANGGAL & WAKTU', 'STATUS']} rows={rows} />
    </>;
  }
  const rows = [
    ['Kardus Bekas', '10 Nov 2026', '12.5 kg', <StatusBadge>Selesai</StatusBadge>, '+Rp 31.250'],
    ['Botol Plastik PET', '05 Nov 2026', '8.0 kg', <StatusBadge>Selesai</StatusBadge>, '+Rp 24.000'],
    ['Kaleng Aluminium', '02 Nov 2026', '3.2 kg', <StatusBadge tone="yellow">Proses</StatusBadge>, '+Rp 16.000'],
  ];
  return <>
    <Card className="balance-banner"><div><span>Selamat datang kembali,</span><strong>Fatih Ahmad Reza El N!</strong><small>TOTAL SALDO AKTIF</small><b>Rp 345.500</b></div><div className="balance-actions"><Button onClick={() => navigate('/setor')}><Plus size={17} />Setor Sampah Baru</Button><Button variant="secondary" onClick={() => navigate('/withdraw')}><ArrowDownLeft size={17} />Tarik Saldo</Button></div></Card>
    <div className="stats-grid two"><StatCard icon={ScaleIcon} label="Total Sampah Disetor" value="124.5 Kg" /><Card className="catalog-promo" onClick={() => navigate('/harga')}><span><Tags size={20} /></span><small>Katalog</small><strong>Harga Sampah</strong></Card></div>
    <div className="section-title"><h2>Aktivitas Terakhir</h2><a onClick={() => navigate('/riwayat')}>Lihat Semua Riwayat <ChevronRight size={14} /></a></div>
    <DataTable headers={['Jenis Sampah', 'Tanggal', 'Berat (Kg)', 'Status', 'Nilai (Rp)']} rows={rows} />
  </>;
}

function ScaleIcon({ size = 20 }) {
  return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M12 3v18M5 7h14M4 20h16M8 7l-4 7h8L8 7Zm8 0-4 7h8l-4-7Z" /><path d="M9 20h6" /></svg>;
}

function FormField({ label, type = 'text', placeholder, defaultValue, options, required = true, hint }) {
  return <label className="form-field"><span>{label}{required && <i> *</i>}</span>
    {options ? <select defaultValue={defaultValue || ''} required={required}><option value="" disabled>Pilih {label.toLowerCase()}</option>{options.map(option => <option key={option}>{option}</option>)}</select>
      : <input type={type} placeholder={placeholder || `Masukkan ${label.toLowerCase()}`} defaultValue={defaultValue} required={required} />}
    {hint && <small>{hint}</small>}
  </label>;
}

function FormPanel({ title, description, children, submitLabel = 'Simpan', onSubmit }) {
  const [notice, setNotice] = useState('');
  function submit(event) {
    event.preventDefault();
    setNotice('Data berhasil disimpan. Terima kasih!');
    onSubmit?.();
  }
  return <Card className="form-card"><PageHeading title={title} description={description} eyebrow="" />
    <form onSubmit={submit}>{children}<div className="form-actions"><Button variant="outline" type="reset">Batal</Button><Button type="submit">{submitLabel}<ArrowRight size={16} /></Button></div>
      {notice && <p className="form-notice" role="status"><Check size={16} />{notice}</p>}
    </form>
  </Card>;
}

function Withdrawal() {
  return <FormPanel title="Tarik Saldo" description="Cairkan saldo hasil tabungan sampahmu ke rekening bank atau e-wallet." submitLabel="Ajukan Penarikan">
    <div className="available-balance"><span>Total Saldo Tersedia</span><strong>Rp 345.500</strong></div>
    <FormField label="Nominal Penarikan (Rp)" type="number" defaultValue="100000" />
    <div className="amount-options">{['50.000', '100.000', '200.000', 'Tarik Semua'].map(amount => <button key={amount} type="button" onClick={event => { const input = event.currentTarget.closest('form').querySelector('input[type="number"]'); input.value = amount === 'Tarik Semua' ? '345500' : amount.replace('.', ''); }}>{amount}</button>)}</div>
    <FormField label="Tujuan Pencairan" options={['GoPay', 'OVO', 'DANA', 'Transfer Bank']} defaultValue="GoPay" />
    <FormField label="Nomor Rekening / No. HP" defaultValue="0812 3456 7890" />
    <FormField label="Nama Pemilik Rekening" defaultValue="Fatih Ahmad Reza El N" />
  </FormPanel>;
}

function Deposit() {
  return <FormPanel title="Setor Sampah Baru" description="Pilih metode penyetoran dan beri tahu admin apa saja yang akan kamu bawa." submitLabel="Kirim Permintaan">
    <h3 className="form-section-title">1. Pilih Metode Penyetoran</h3>
    <div className="choice-grid"><label className="choice-card"><input type="radio" name="method" defaultChecked /><span>🚶</span><strong>Antar ke Bank Sampah</strong><small>Bawa langsung sampahmu ke lokasi pengepul E-Bank Sampah FILKOM.</small></label><label className="choice-card"><input type="radio" name="method" /><span>🚚</span><strong>Minta Dijemput</strong><small>Admin akan datang menjemput ke alamatmu (minimal total berat 10 Kg).</small></label></div>
    <h3 className="form-section-title">2. Kategori Sampah (Pilih yang dibawa)</h3>
    <div className="check-grid">{['Botol / Plastik PET', 'Kardus & Kertas HVS', 'Logam & Kaleng Aluminium', 'Beling / Botol Kaca'].map(item => <label key={item}><input type="checkbox" />{item}</label>)}</div>
    <h3 className="form-section-title">3. Jadwal & Keterangan</h3>
    <FormField label="Rencana Tanggal Setor" type="date" defaultValue="2026-11-15" />
    <FormField label="Catatan Tambahan (Opsional)" required={false} placeholder="Contoh: Sampah kardus sudah diikat rapi." />
  </FormPanel>;
}

function PriceCatalog({ admin = false }) {
  const prices = [
    ['♧', 'Kaleng Aluminium', 'LOGAM', 'Kaleng minuman bekas, bersih dari cairan.', 'Rp 5.000', '+ Rp 500'],
    ['▤', 'Kertas HVS / Buku Bekas', 'KERTAS', 'Kertas putih bekas catatan kuliah, print, atau buku tulis.', 'Rp 3.500', 'Stabil'],
    ['♜', 'Botol Plastik PET Bening', 'PLASTIK', 'Botol air mineral bening. Lepaskan label dan tutup botol.', 'Rp 3.000', '+ Rp 200'],
    ['⬡', 'Kardus Cokelat', 'KERTAS', 'Kardus bekas paket yang bersih dan sudah dilipat.', 'Rp 2.500', 'Stabil'],
  ];
  const [category, setCategory] = useState('Semua Kategori (6)');
  const [search, setSearch] = useState('');
  const list = prices.filter(item => (category.startsWith('Semua') || item[2] === category.toUpperCase()) && item[1].toLowerCase().includes(search.toLowerCase()));
  return <><PageHeading title={admin ? 'Katalog Harga Sampah' : 'Katalog Harga Sampah'} description={admin ? 'Kelola jenis, syarat kondisi, dan perbarui harga beli sampah per kilogram.' : 'Daftar nilai tukar sampah terpilah per kilogram yang berlaku saat ini.'} actions={admin && <Button><Plus size={16} />Tambah Jenis</Button>} />
    <div className="catalog-toolbar">{['Semua Kategori (6)', 'Plastik', 'Kertas', 'Logam'].map(item => <button key={item} className={category === item ? 'active' : ''} onClick={() => setCategory(item)}>{item}</button>)}<label className="search-field"><Search size={16} /><input value={search} onChange={e => setSearch(e.target.value)} placeholder="Cari jenis sampah..." /></label></div>
    <div className="price-grid">{list.map(([icon, name, tag, description, price, trend]) => <Card className="price-card" key={name}><span className="price-icon">{icon}</span><StatusBadge tone="neutral">{tag}</StatusBadge><h2>{name}</h2><p>{description}</p><div className="price-footer"><small>Harga Beli<strong>{price}<em> / kg</em></strong></small><StatusBadge>{trend}</StatusBadge></div></Card>)}</div>
  </>;
}

function Profile() {
  return <FormPanel title="Halaman Profil" description="Kelola informasi pribadi, alamat, dan metode pencairan saldo." submitLabel="Simpan Perubahan">
    <div className="profile-summary"><div className="avatar">FA</div><div><strong>Fatih Ahmad Reza El N</strong><small>ID: NAS-982103 · <StatusBadge>Terverifikasi</StatusBadge></small></div><Button variant="outline">Ubah Foto</Button></div>
    <h3 className="form-section-title">Data Diri & Alamat</h3>
    <div className="form-grid"><FormField label="Nama Lengkap" defaultValue="Fatih Ahmad Reza El N" /><FormField label="NIK (Terkunci)" defaultValue="3174055205980003" required={false} /><FormField label="Nomor WhatsApp" defaultValue="0812 3456 7890" /><FormField label="Email" type="email" defaultValue="nada.putri@gmail.com" /><FormField label="Alamat Penjemputan" defaultValue="Jl. Soekarno Hatta No. 12, Lowokwaru, Kota Malang" /></div>
  </FormPanel>;
}

function Transactions({ admin = false }) {
  const rows = admin ? [
    ['Ayu Wulandari', 'Setoran Sampah · Antar Sendiri', 'Menunggu Timbangan', <StatusBadge tone="yellow">Pending Fisik</StatusBadge>],
    ['Siti Fatimah', 'Setoran Sampah · Jemput', '+ Rp 46.500', <StatusBadge>Selesai</StatusBadge>],
    ['Dimas Ramadhan', 'Tarik Saldo · Transfer Bank', 'Rp 100.000', <StatusBadge tone="red">Ditolak</StatusBadge>],
  ] : [
    ['Setor Sampah (Kardus)', '10 Nov 2026 · 14:30 WIB', 'Antar Sendiri · 12.5 Kg', <StatusBadge>Selesai</StatusBadge>],
    ['Tarik Saldo (GoPay)', '08 Nov 2026 · 09:15 WIB', 'Pencairan via GoPay', <StatusBadge tone="yellow">Diproses Admin</StatusBadge>],
    ['Permintaan Jemput Sampah', '05 Nov 2026 · 10:00 WIB', 'Minta Dijemput (Kos Suhat)', <StatusBadge tone="yellow">Menunggu</StatusBadge>],
    ['Setor Sampah (Botol)', '28 Okt 2026 · 16:45 WIB', 'Antar Sendiri · 8.0 Kg', <StatusBadge>Selesai</StatusBadge>],
  ];
  return <><PageHeading title={admin ? 'Manajemen Transaksi' : 'Riwayat Transaksi'} description={admin ? 'Verifikasi setoran fisik dan setujui penarikan saldo nasabah.' : 'Pantau seluruh aktivitas setoran sampah dan penarikan saldomu.'} actions={<Button variant="outline"><FileText size={16} />Unduh Laporan PDF</Button>} /><DataTable headers={admin ? ['NASABAH', 'JENIS TRANSAKSI', 'NILAI (RP)', 'STATUS'] : ['TRANSAKSI', 'TANGGAL & WAKTU', 'METODE / KETERANGAN', 'STATUS']} rows={rows} /></>;
}

function Management({ route }) {
  const members = [
    ['Budi Santoso · EBS-2611-001', '0812-3456-7890', '145.5 Kg', 'Rp 250.000', <StatusBadge>Aktif</StatusBadge>],
    ['Ayu Wulandari · EBS-2611-002', '0856-7890-1234', '89.2 Kg', 'Rp 125.500', <StatusBadge>Aktif</StatusBadge>],
    ['Dimas Ramadhan · EBS-2610-088', '0877-1122-3344', '12.0 Kg', 'Rp 0', <StatusBadge tone="neutral">Nonaktif</StatusBadge>],
    ['Siti Fatimah · EBS-2611-004', '0813-9988-7766', '230.8 Kg', 'Rp 410.200', <StatusBadge>Aktif</StatusBadge>],
  ];
  const waste = [
    ['Botol Plastik Bening (PET)', 'Plastik', 'Bersih, tutup botol dilepas', 'Rp 4.500 / kg', '↑ Rp 500'],
    ['Kardus Bekas Lembaran', 'Kertas & Kardus', 'Tidak basah, tidak berminyak', 'Rp 2.000 / kg', 'Tetap'],
    ['Kaleng Aluminium Bekas', 'Logam', 'Kosong dan bersih dari sisa makanan', 'Rp 8.000 / kg', '↓ Rp 200'],
  ];
  const isMembers = route.includes('nasabah');
  return <><PageHeading title={isMembers ? 'Manajemen Data Nasabah' : 'Katalog Jenis Sampah'} description={isMembers ? 'Kelola informasi, saldo, dan status keanggotaan nasabah E-Bank Sampah.' : 'Kelola kategori, syarat kondisi, dan nilai beli per kilogram.'} actions={<><Button variant="outline"><FileText size={16} />{isMembers ? 'Export Data' : 'Riwayat Perubahan'}</Button><Button><Plus size={16} />{isMembers ? 'Tambah Nasabah' : 'Tambah Jenis'}</Button></>} />
    {isMembers && <Card className="filter-bar">Status: <Button variant="outline">Semua</Button><Button variant="outline">Aktif</Button><Button variant="outline">Nonaktif</Button><select aria-label="Urutkan"><option>Urutkan: Terkini</option><option>Nama A-Z</option></select></Card>}
    <DataTable headers={isMembers ? ['PROFIL NASABAH', 'KONTAK', 'TOTAL SAMPAH (KG)', 'SALDO AKTIF', 'STATUS'] : ['JENIS & KATEGORI', 'SYARAT KONDISI', 'HARGA BELI', 'TREN']} rows={isMembers ? members : waste} />
  </>;
}

function RegistrationFlow() {
  const [step, setStep] = useState(1);
  const [complete, setComplete] = useState(false);
  if (complete) return <div className="success-panel"><span><Check size={26} /></span><h1>Pendaftaran Berhasil!</h1><p>Selamat bergabung! Akun Anda sudah dibuat. Mari mulai langkah baik untuk FILKOM.</p><Button onClick={() => navigate('/login')}>Masuk ke Akun <ArrowRight size={17} /></Button></div>;
  return <><div className="stepper">{['Data Diri', 'Alamat', 'Berhasil'].map((label, index) => <div key={label} className={index + 1 <= step ? 'step active' : 'step'}><b>{index + 1 < step ? <Check size={15} /> : index + 1}</b><span>{label}</span>{index < 2 && <i />}</div>)}</div>
    <div className="registration-content"><span className="eyebrow">LANGKAH {step} DARI 3</span><h1>{step === 1 ? 'Kenalan dulu, yuk.' : 'Di mana Anda tinggal?'}</h1><p>{step === 1 ? 'Isi data diri Anda dengan benar sesuai KTP untuk mulai menjadi nasabah.' : 'Lengkapi alamat domisili dan pilih bank sampah yang paling dekat dengan Anda.'}</p>
      <form className="form-grid" onSubmit={event => { event.preventDefault(); if (step < 2) setStep(step + 1); else setComplete(true); }}>
        {step === 1 ? <><FormField label="Nama lengkap" /><FormField label="NIK" /><FormField label="Tanggal lahir" type="date" /><FormField label="Jenis kelamin" options={['Laki-laki', 'Perempuan']} /><FormField label="Nomor WhatsApp" /><FormField label="Email" type="email" /><FormField label="Foto KTP" type="file" hint="JPG, PNG, atau PDF · Maks. 5 MB" /></>
          : <><FormField label="Kota administrasi" options={['Jakarta Selatan', 'Jakarta Barat', 'Malang']} /><FormField label="Kecamatan" options={['Tebet', 'Lowokwaru', 'Kebayoran']} /><FormField label="Kelurahan" options={['Tebet Barat', 'Sumbersari', 'Kebayoran Baru']} /><FormField label="RT" /><FormField label="RW" /><FormField label="Alamat lengkap" /><FormField label="Bank sampah terdekat" options={['Bank Sampah FILKOM', 'Bank Sampah Tebet Berseri']} /></>}
        <div className="form-actions"><Button variant="outline" onClick={() => step === 1 ? navigate('/') : setStep(1)}><ArrowLeft size={16} />Kembali</Button><Button type="submit">{step === 1 ? 'Lanjut' : 'Daftar'}<ArrowRight size={16} /></Button></div>
      </form>
    </div>
  </>;
}

function AuthForm({ route }) {
  const isLogin = route === '/login';
  return <Card className="auth-card"><div className="auth-heading"><h1>{isLogin ? 'Selamat Datang!' : 'Buat Akun Baru'}</h1><p>{isLogin ? 'Masuk untuk mulai kelola tabungan sampahmu.' : 'Langkah awal menjadi pahlawan lingkungan.'}</p></div>
    <form onSubmit={event => { event.preventDefault(); navigate(isLogin ? '/dashboard' : '/daftar'); }}>
      {!isLogin && <FormField label="Nama Lengkap" />}
      <FormField label={isLogin ? 'Email' : 'Email Aktif'} type="email" placeholder={isLogin ? 'Masukkan email aktif' : 'Contoh: nasabah@email.com'} />
      <FormField label={isLogin ? 'Kata Sandi' : 'Buat Kata Sandi'} type="password" placeholder={isLogin ? 'Masukkan kata sandi' : 'Minimal 8 karakter'} />
      {!isLogin && <FormField label="Konfirmasi Kata Sandi" type="password" placeholder="Ulangi kata sandi" />}
      {isLogin && <a className="forgot-link">Lupa Kata Sandi?</a>}
      <Button type="submit" className="full-button">{isLogin ? 'Masuk' : 'Lanjut Isi Data Diri'}</Button>
    </form><p className="auth-switch">{isLogin ? 'Belum punya akun?' : 'Sudah punya akun?'} <a onClick={() => navigate(isLogin ? '/register' : '/login')}>{isLogin ? 'Daftar' : 'Masuk'}</a></p>
  </Card>;
}

function InfoPage({ route }) {
  const content = {
    '/tentang': ['Tentang E-Bank Sampah', 'Platform digital untuk mendukung pengelolaan sampah dan mendorong kebiasaan daur ulang yang berkelanjutan.'],
    '/lokasi': ['Lokasi Bank Sampah', 'Temukan bank sampah terdekat dan jadwal layanan di lingkungan Anda.'],
    '/helpdesk': ['Pusat Helpdesk', 'Tim kami siap membantu pertanyaan seputar pendaftaran, setoran, dan pencairan saldo.'],
  }[route];
  return <div className="info-page"><span className="success-icon"><Headset size={24} /></span><h1>{content[0]}</h1><p>{content[1]}</p><Button onClick={() => navigate('/dashboard')}>Kembali ke Dashboard</Button></div>;
}

function App() {
  const [route, setRoute] = useState(window.location.pathname);
  const [menuOpen, setMenuOpen] = useState(false);
  useEffect(() => {
    const onPopState = () => { setRoute(window.location.pathname); setMenuOpen(false); };
    window.addEventListener('popstate', onPopState);
    return () => window.removeEventListener('popstate', onPopState);
  }, []);

  if (route === '/') return <HomePage />;
  let page;
  if (route === '/login' || route === '/register') page = <AuthForm route={route} />;
  else if (route === '/daftar') page = <RegistrationFlow />;
  else if (route === '/dashboard') page = <Dashboard />;
  else if (route === '/admin') page = <Dashboard admin />;
  else if (route === '/setor') page = <Deposit />;
  else if (route === '/withdraw') page = <Withdrawal />;
  else if (route === '/riwayat') page = <Transactions />;
  else if (route === '/admin/transaksi') page = <Transactions admin />;
  else if (route === '/harga') page = <PriceCatalog />;
  else if (route === '/admin/jenis-sampah') page = <Management route={route} />;
  else if (route === '/admin/nasabah') page = <Management route={route} />;
  else if (route === '/profil') page = <Profile />;
  else if (['/tentang', '/lokasi', '/helpdesk'].includes(route)) page = <InfoPage route={route} />;
  else page = <InfoPage route="/tentang" />;

  return <Shell route={route} menuOpen={menuOpen} onToggleMenu={() => setMenuOpen(open => !open)}>{page}</Shell>;
}

createRoot(document.getElementById('root')).render(<App />);
