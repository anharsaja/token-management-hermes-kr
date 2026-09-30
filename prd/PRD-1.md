# PRD-1 — Token Management: Project Foundation & Agent Management

Versi: 1.0
Tanggal: 30 September 2026
Status: Draft

---

## Overview

Project "Token Management" adalah aplikasi web lokal berbasis Laravel + Inertia.js + Vue 3
yang berfungsi sebagai dashboard untuk memantau daftar AI agent dan riwayat pemakaian token.
Semua data diinput manual oleh user. Tidak ada integrasi API eksternal.
Project ini bersifat showcase/personal tool, berjalan di localhost saja.

PRD-1 mencakup: setup fondasi proyek, autentikasi sederhana, dan modul Agent (CRUD).

---

## Scope PRD-1

In-scope:
- Inisialisasi proyek Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS
- Setup Docker (docker-compose untuk PHP + Nginx + Node)
- Autentikasi lokal (single user, login/logout)
- Modul Agent: Create, Read, Update, Delete
- Layout dasar dengan navigasi sidebar

Out-of-scope (dikerjakan di PRD berikutnya):
- Token usage / balance tracking
- Dashboard statistik / chart
- Multi-user / role management

---

## Actors & Use Cases

Actor: Owner (satu-satunya user, login lokal)

**UC-01 Login**
- Trigger : Owner membuka app di browser
- Flow    : Isi email + password → redirect ke dashboard
- Outcome : Owner masuk ke halaman utama

**UC-02 Lihat Daftar Agent**
- Trigger : Owner klik menu "Agents"
- Flow    : Sistem tampilkan tabel semua agent
- Outcome : Tabel berisi nama, provider, model default, status, tanggal dibuat

**UC-03 Tambah Agent**
- Trigger : Owner klik tombol "Add Agent"
- Flow    : Isi form → submit → simpan ke DB
- Outcome : Agent baru muncul di tabel

**UC-04 Edit Agent**
- Trigger : Owner klik ikon edit pada row agent
- Flow    : Form terisi data lama → ubah → submit
- Outcome : Data agent terupdate

**UC-05 Hapus Agent**
- Trigger : Owner klik ikon hapus pada row agent
- Flow    : Konfirmasi dialog → confirm → hapus
- Outcome : Agent hilang dari tabel (soft delete)

**UC-06 Logout**
- Trigger : Owner klik "Logout"
- Outcome : Session dihapus, redirect ke halaman login

---

## Data Model

### Tabel: users

| Kolom          | Tipe          | Keterangan                  |
|----------------|---------------|-----------------------------|
| id             | BIGINT PK     | Auto increment              |
| name           | VARCHAR(100)  |                             |
| email          | VARCHAR(150)  | Unique                      |
| password       | VARCHAR(255)  | Hashed                      |
| remember_token | VARCHAR(100)  | Nullable                    |
| created_at     | TIMESTAMP     |                             |
| updated_at     | TIMESTAMP     |                             |

Catatan: Di-seed satu user default saat fresh install.

### Tabel: agents

| Kolom         | Tipe                       | Keterangan                          |
|---------------|----------------------------|-------------------------------------|
| id            | BIGINT PK                  | Auto increment                      |
| name          | VARCHAR(100)               | e.g. "Kiro Dev Assistant"           |
| provider      | VARCHAR(100)               | e.g. "OpenAI", "Anthropic"          |
| model_default | VARCHAR(100)               | e.g. "gpt-4o", "claude-3-5-sonnet" |
| description   | TEXT                       | Nullable, catatan bebas             |
| status        | ENUM('active','inactive')  | Default 'active'                    |
| deleted_at    | TIMESTAMP                  | Nullable, soft delete               |
| created_at    | TIMESTAMP                  |                                     |
| updated_at    | TIMESTAMP                  |                                     |

---

## API / Route Contract

Semua route di bawah middleware `auth`.

| Method | URI                  | Keterangan                                                      |
|--------|----------------------|-----------------------------------------------------------------|
| GET    | /                    | Redirect ke /dashboard                                          |
| GET    | /dashboard           | Render Inertia page Dashboard (placeholder)                     |
| GET    | /agents              | Render Inertia page Agents/Index, props: { agents: paginated }  |
| POST   | /agents              | Store agent baru, redirect back + flash message                 |
| GET    | /agents/{id}/edit    | Render Inertia page Agents/Edit, props: { agent }               |
| PUT    | /agents/{id}         | Update agent, redirect ke /agents + flash                       |
| DELETE | /agents/{id}         | Soft delete agent, redirect ke /agents + flash                  |

Auth routes (Laravel Breeze default):

| Method | URI      |
|--------|----------|
| GET    | /login   |
| POST   | /login   |
| POST   | /logout  |

---

## UI Components (Vue)

**Layout/AppLayout.vue**
- Sidebar dengan nav: Dashboard, Agents
- Topbar: nama user + tombol logout
- Slot konten utama

**Pages/Agents/Index.vue**
- Tabel: Name, Provider, Model Default, Status (badge), Created At, Actions
- Tombol "Add Agent" → navigate ke form
- Konfirmasi hapus: modal sederhana

**Pages/Agents/Form.vue** (dipakai untuk Create & Edit)
- Field: Name (required), Provider (required), Model Default (required),
         Description (opsional), Status (toggle active/inactive)
- Tombol Save + Cancel

**Pages/Dashboard.vue**
- Placeholder "Coming soon — token stats will appear here"

---

## Acceptance Criteria

- AC-01: User tidak bisa mengakses halaman manapun tanpa login.
- AC-02: Form agent validasi server-side: name, provider, model_default wajib diisi, max 100 karakter.
- AC-03: Hapus agent adalah soft delete — data tidak hilang dari DB, hanya tidak muncul di UI.
- AC-04: Flash message muncul setelah create/update/delete berhasil.
- AC-05: Tabel agent paginasi 15 row per halaman.
- AC-06: Status badge tampil berbeda warna: active = hijau, inactive = abu.

---

## Tech Stack & Constraints

| Komponen     | Versi                                 |
|--------------|---------------------------------------|
| Laravel      | 11.x                                  |
| Inertia.js   | 2.x                                   |
| Vue          | 3.x (Composition API)                 |
| Tailwind CSS | 3.x                                   |
| Database     | SQLite                                |
| Auth         | Laravel Breeze (Inertia + Vue preset) |
| Node.js      | >= 20                                 |
| PHP          | >= 8.2                                |
| Docker       | Docker + Docker Compose v2            |

### Docker Service Composition

| Service  | Image              | Keterangan                              |
|----------|--------------------|-----------------------------------------|
| app      | php:8.2-fpm        | PHP-FPM menjalankan Laravel             |
| nginx    | nginx:alpine       | Web server, proxy ke PHP-FPM            |
| node     | node:20-alpine     | Build frontend (Vite), dev mode         |

- SQLite dipakai — tidak perlu service database terpisah
- Volume mount: source code di-mount ke container supaya perubahan langsung reflect
- Port: Nginx expose port 8080 → akses via http://localhost:8080
- `docker-compose up -d` untuk menjalankan semua service
- `docker-compose exec app php artisan migrate:fresh --seed` untuk setup DB awal

---

## Assumptions & Open Questions

**Assumptions:**
- A1: Satu user saja — tidak perlu register page, cukup seed DB dengan satu akun default.
- A2: SQLite dipakai supaya zero-config di lokal; tidak perlu MySQL/Postgres.
- A3: "Provider" dan "Model Default" adalah free-text input, bukan dropdown dari list tetap.

**Open Questions:**
- OQ-01: Apakah field "provider" perlu ada daftar pilihan tetap (dropdown), atau free-text sudah cukup?
- OQ-02: Apakah satu agent bisa punya lebih dari satu model (multi-model per agent),
         atau cukup satu model default per agent untuk saat ini?
