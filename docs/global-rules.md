# Global Project Rules — Token Management

Versi: 1.0
Tanggal: 30 September 2026

Dokumen ini adalah aturan global yang berlaku untuk semua agent (System Analyst, Coder, QA, Lead)
di seluruh siklus hidup project ini. Semua keputusan teknis dan proses harus mengacu ke sini.

---

## Tech Stack (Locked)

| Komponen     | Pilihan               | Keterangan                              |
|--------------|-----------------------|-----------------------------------------|
| Backend      | Laravel 11.x          | PHP >= 8.2                              |
| Frontend     | Vue 3.x               | Composition API + <script setup>        |
| Bridge       | Inertia.js 2.x        | Server-driven SPA, tanpa REST API       |
| Styling      | Tailwind CSS 3.x      | Utility-first, tidak pakai CSS custom   |
| Database     | SQLite                | File lokal, zero-config                 |
| Auth         | Laravel Breeze        | Preset Inertia + Vue                    |
| Node.js      | >= 20                 | Untuk build frontend (Vite)             |
| Build tool   | Vite                  | Sudah include di Breeze                 |
| Docker       | Docker + Compose v2   | Wajib — semua service jalan via Docker  |

### Docker Service Composition

| Service | Image           | Keterangan                      |
|---------|-----------------|---------------------------------|
| app     | php:8.2-fpm     | PHP-FPM menjalankan Laravel     |
| nginx   | nginx:alpine    | Web server, proxy ke PHP-FPM    |
| node    | node:20-alpine  | Build frontend (Vite), dev mode |

- Akses app via http://localhost:8080
- Tidak ada service DB terpisah — SQLite cukup
- Jalankan: `docker-compose up -d`
- Setup DB: `docker-compose exec app php artisan migrate:fresh --seed`

Tidak boleh menambah dependency besar tanpa persetujuan Lead.

---

## Konvensi Kode

### PHP / Laravel
- PSR-12 code style
- Gunakan Form Request untuk semua validasi (bukan inline di controller)
- Controller hanya boleh berisi: validasi → service/model call → return response
- Gunakan Resource Controller (7 method standar)
- Soft delete wajib untuk semua entitas utama
- Migration tidak boleh dimodifikasi setelah di-commit — buat migration baru

### Vue / JavaScript
- Composition API dengan <script setup> di semua komponen
- Nama komponen: PascalCase (AgentForm.vue, bukan agentForm.vue)
- Props selalu diberi type dan default
- Tidak ada logic bisnis di dalam template — pindah ke computed/composable
- Gunakan Inertia Link (<Link>) bukan <a href> untuk navigasi internal

### Penamaan
- Database kolom : snake_case
- PHP class       : PascalCase
- PHP method/var  : camelCase
- Vue component   : PascalCase
- Vue composable  : useCamelCase (e.g. useAgents.js)
- File migration  : timestamp_create_table_name_table.php

---

## Struktur Direktori Project

```
tokenManagement/         <- root project ini (dokumentasi & planning)
├── prd/                 <- semua PRD per fase
├── docs/                <- aturan global, arsitektur, referensi
└── agent/               <- skill file tiap role agent

Laravel project akan dibuat di subfolder: tokenManagement/app/
(atau direktori terpisah sesuai arahan Lead saat eksekusi PRD-1)
```

---

## Workflow Antar Agent

1. System Analyst menghasilkan PRD → simpan di prd/PRD-N.md
2. Lead membaca PRD → breakdown jadi task → assign ke Coder
3. Coder implementasi → sesuai acceptance criteria di PRD
4. QA verifikasi → checklist AC satu per satu, tulis laporan bug
5. Lead review hasil QA → merge / minta fix

Tidak ada agent yang skip step. Coder tidak boleh langsung coding tanpa PRD tersedia.
QA tidak boleh test sebelum Coder selesai satu modul utuh.

---

## Aturan PRD

- Satu PRD = satu fase / satu modul besar
- PRD dikerjakan bertahap: PRD-1 dulu selesai, baru PRD-2 dimulai (boleh paralel di fase mature)
- Setiap PRD wajib punya: Overview, Scope, Actors & Use Cases, Data Model, API Contract, AC, Assumptions
- Open Questions di PRD harus dijawab sebelum Coder mulai

---

## Aturan Database

- Selalu gunakan migration — tidak ada perubahan DB manual
- Setiap tabel entitas utama wajib punya: id, created_at, updated_at, deleted_at (soft delete)
- Foreign key wajib didefinisikan di migration
- Seed data minimal: 1 user default (email: admin@local.test, password: password)

---

## Deployment & Environment

- Project ini HANYA berjalan di localhost — tidak ada production deploy
- .env tidak di-commit ke git
- SQLite file (database/database.sqlite) tidak di-commit ke git
- `php artisan serve` untuk jalankan backend, `npm run dev` untuk frontend

---

## Hal yang Tidak Boleh Dilakukan

- Menambah REST API endpoint (gunakan Inertia, bukan API JSON murni)
- Menggunakan CSS framework selain Tailwind
- Membuat fitur di luar scope PRD yang sedang aktif
- Hardcode credential atau secret di kode
- Modifikasi migration yang sudah ada
