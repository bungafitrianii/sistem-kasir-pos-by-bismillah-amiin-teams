# Frontend Halaman Login - Amanah Store (Universal Responsive)

Implementasi Frontend Halaman Login untuk **Amanah Store (Sistem Kasir & Stok Toko Kelontong)** dengan desain UI/UX yang **100% identik dengan desain acuan** di folder `Assets/Amanah Store - Login.png` dan **sepenuhnya responsif** di seluruh perangkat (Mobile, Tablet, Layar POS, Laptop, dan Desktop 4K).

---

## 📁 Struktur Berkas Proyek

```
source-code/
├── assets/
│   └── images/
│       └── logo.png         # Logo resmi Amanah Store (gradien squircle + badge petir)
├── index.html               # Struktur semantik HTML5, SVG icons, & tipografi Plus Jakarta Sans
├── style.css                # Styling universal all-device, responsive breakpoints, & ambient glow
├── script.js                # Interaktivitas (toggle password, form state, siap API Backend)
└── README.md                # Dokumentasi proyek
```

---

## 📱 & 🖥️ Dukungan Responsif Semua Perangkat

- **Smartphone Kecil (≤ 380px)**: iPhone SE, Galaxy Mini — ukuran font & padding fleksibel, bebas dari scroll samping.
- **Smartphone Modern (390px - 480px)**: iPhone 13/14/15/16/17, Galaxy S — rasio visual 1:1 identik dengan desain acuan.
- **Tablet & Mesin Kasir POS (600px - 1024px)**: iPad & Layar Sentuh POS kasir — tombol & input empuk dengan target sentuh ideal.
- **Laptop & Desktop (≥ 1025px)**: Layar 1080p, 2K, 4K — kartu mengapung anggun di tengah (lebar ideal 450px) dengan latar ambient glow.

---

## 🔌 Integrasi Backend

Endpoint backend siap dihubungkan di berkas `script.js`:
- Endpoint: `POST /api/auth/login`
- Parameter dikirim: `{ username, password, remember }`
- Dukungan respons: CSRF Token & Session User redirect ke Dashboard Kasir.
