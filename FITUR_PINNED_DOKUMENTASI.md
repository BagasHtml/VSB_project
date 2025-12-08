# 📌 Fitur Pinned - Dokumentasi

## Deskripsi
Fitur **Pinned** memungkinkan developer/admin untuk menampilkan post tertentu di atas halaman utama forum. Post yang di-pin akan selalu muncul di bagian atas daftar post, terlepas dari kapan post tersebut dibuat.

## Akses
- **Hanya untuk:** Developer & Admin (User dengan level ≥ 50)
- **Role Check:** Dilakukan berdasarkan `level` user di tabel `users`

## Fitur

### 1. Badge Pinned
- Post yang ter-pin akan menampilkan badge **"📌 Pinned"** di sudut kanan atas post card
- Badge memiliki styling gradient kuning-amber dengan ikon pin
- Otomatis muncul ketika post di-pin

### 2. Button Pin/Unpin
- Tombol hanya muncul untuk developer/admin
- Tombol terletak di bagian action (sama seperti comment, like, share, delete)
- Warna berubah menjadi **kuning** ketika post sudah di-pin
- Text berubah dari "Pin" menjadi "Unpin"

### 3. Ordering Otomatis
- Post ter-pin muncul di **paling atas**
- Post baru tidak ter-pin akan muncul di bawah post ter-pin
- Urutan post ter-pin tetap berdasarkan `created_at` DESC
- Urutan post tidak ter-pin juga berdasarkan `created_at` DESC

## Database

### Struktur Tabel
```sql
ALTER TABLE posts ADD COLUMN is_pinned TINYINT(1) DEFAULT 0 AFTER caption;
```

**Field Details:**
- `is_pinned` - TINYINT(1) DEFAULT 0
  - `0` = Post tidak di-pin
  - `1` = Post ter-pin

## File yang Diubah/Dibuat

### 1. `/service/api/pin_post.php` (BARU)
Handler untuk request pin/unpin post
```php
- Method: POST
- Parameter: post_id
- Response: JSON
  {
    "status": "ok",
    "is_pinned": 0 atau 1
  }
- Error Handling:
  - "not_logged" - User belum login
  - "not_authorized" - User bukan developer/admin
  - "no_post" - Post ID kosong
  - "post_not_found" - Post tidak ditemukan
  - "update_failed" - Gagal update database
```

### 2. `/View/halaman_utama.php` (DIMODIFIKASI)
- Tambah check `$is_developer` (level >= 50)
- Ubah query ORDER BY menjadi `ORDER BY posts.is_pinned DESC, posts.created_at DESC`
- Tambah badge pinned di post card
- Tambah button pin/unpin
- Tambah JavaScript handler untuk pin/unpin

### 3. `/setup_pinned.php` (HELPER)
Script untuk setup database (jalankan sekali saja)

## Cara Menggunakan

### Setup Database (Jalankan Sekali)
```
1. Buka browser: http://localhost/VSB_project/setup_pinned.php
2. Jika berhasil, akan muncul: "✅ Kolom is_pinned berhasil ditambahkan ke tabel posts!"
```

### Menggunakan Fitur
```
1. Login sebagai user dengan level ≥ 50 (developer/admin)
2. Di setiap post card, cari tombol "Pin" di bagian action
3. Klik tombol "Pin" untuk menampilkan post di atas
4. Tombol akan berubah menjadi "Unpin" jika post sudah ter-pin
5. Halaman akan auto-reload untuk update ordering
```

## Security
✅ **Authorization Check:**
- Server-side check di `pin_post.php`
- Memverifikasi user adalah developer/admin (level >= 50)
- POST request dengan CSRF protection via session

✅ **Data Validation:**
- Post ID harus valid dan integer
- User harus login
- Post harus ada di database

## Browser Compatibility
- Modern browsers dengan ES6+ support
- Fetch API (IE 11 tidak support, tapi custom fallback bisa ditambahkan)

## Performance
- Query menggunakan ORDER BY TINYINT (sangat cepat)
- Toggle status hanya update 1 baris di database
- Auto-reload page untuk konsistensi data

## Future Enhancements
- [ ] Tambah limit jumlah post yang bisa di-pin
- [ ] Multiple pin categories (announcement, important, etc)
- [ ] Pin/Unpin animation tanpa page reload
- [ ] Audit log untuk track siapa pin post
- [ ] Pin expiration date
- [ ] Admin panel untuk manage pinned posts

---
**Dibuat untuk:** Knowledge Battle Forum
**Developer:** VSB Admin Team
**Last Updated:** December 8, 2025
