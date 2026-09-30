# Token Management

Aplikasi web lokal untuk memantau daftar AI agent dan riwayat pemakaian token.
Dibangun dengan Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS, berjalan via Docker.

---

## Tech Stack

| Komponen     | Versi                 |
|--------------|-----------------------|
| Laravel      | 11.x                  |
| Inertia.js   | 2.x                   |
| Vue          | 3.x (Composition API) |
| Tailwind CSS | 3.x                   |
| Database     | SQLite                |
| Auth         | Laravel Breeze        |
| Node.js      | 20                    |
| PHP          | 8.2                   |
| Docker       | Docker + Compose v2   |

---

## Prasyarat

- Docker & Docker Compose v2 terinstall
- Port 8080 tersedia di host

---

## Cara Menjalankan

1. Clone repo dan masuk ke direktori project:

       git clone <repo-url> tokenManagement
       cd tokenManagement

2. Salin file environment:

       cp .env.example .env

3. Jalankan semua service:

       docker compose up -d

4. Setup database (migrate + seed user default):

       docker compose exec app php artisan migrate:fresh --seed

5. Buka browser: http://localhost:8080

---

## Login Default

| Field    | Value             |
|----------|-------------------|
| Email    | owner@example.com |
| Password | password          |

Ganti password setelah login pertama via halaman Profile.

---

## Fitur (PRD-1)

- Autentikasi lokal (login / logout) — single user
- Modul Agent: Create, Read, Update, Delete (soft delete)
- Tabel agent dengan pagination 15 row/halaman
- Status badge: active (hijau) / inactive (abu)
- Flash message setelah create / update / delete
- Layout sidebar dengan navigasi Dashboard & Agents

---

## Struktur Direktori Penting

    app/
      Http/Controllers/AgentController.php   — CRUD agent
      Models/Agent.php                        — Model dengan SoftDeletes
    database/
      migrations/                             — Skema tabel
      seeders/DatabaseSeeder.php              — User default
    resources/js/
      Layouts/AppLayout.vue                   — Sidebar layout
      Pages/
        Dashboard.vue                         — Placeholder dashboard
        Agents/
          Index.vue                           — Tabel + delete modal
          Form.vue                            — Form create & edit
    docker/
      nginx/default.conf                      — Konfigurasi Nginx
      php/Dockerfile                          — Image PHP-FPM
    docker-compose.yml

---

## Perintah Berguna

    # Jalankan semua service
    docker compose up -d

    # Reset database
    docker compose exec app php artisan migrate:fresh --seed

    # Tail log Laravel
    docker compose exec app php artisan pail

    # Masuk ke shell container
    docker compose exec app bash

    # Jalankan tests
    docker compose exec app php artisan test

---

## Pengembangan Lokal (tanpa Docker)

Pastikan PHP >= 8.2 dengan extension pdo_sqlite, dan Node >= 20, lalu:

    composer install
    cp .env.example .env
    php artisan key:generate
    touch database/database.sqlite
    php artisan migrate:fresh --seed
    npm install && npm run dev
    php artisan serve

App berjalan di http://localhost:8000.

---

## Roadmap

- PRD-2: Token usage & balance tracking
- PRD-3: Dashboard statistik & chart
