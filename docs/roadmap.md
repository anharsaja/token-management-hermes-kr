# Roadmap — Token Management

Versi: 1.0
Tanggal: 30 September 2026

---

## Gambaran Umum

Aplikasi dashboard lokal untuk memantau pemakaian token AI agent secara manual.
Stack: Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS + SQLite + Docker.
Dikerjakan bertahap per PRD — Coder dan Analyst boleh jalan paralel mulai PRD-2.

---

## Fase & Status

| Fase  | PRD    | Modul                          | Depends On    | Status      |
|-------|--------|--------------------------------|---------------|-------------|
| 1     | PRD-1  | Foundation, Docker, Auth,      | —             | Ready       |
|       |        | Agent Management (CRUD)        |               |             |
| 2     | PRD-2  | Token Usage (CRUD + filter)    | PRD-1         | Ready       |
|       |        | Provider Balance (CRUD)        |               |             |
| 3     | PRD-3  | Dashboard: summary cards,      | PRD-1, PRD-2  | Ready       |
|       |        | tabel ringkasan, filter periode|               |             |
| 4     | PRD-4  | Charts (line, bar, doughnut),  | PRD-1–3       | Ready       |
|       |        | Export CSV                     |               |             |

---

## Alur Pengerjaan

    PRD-1 ──► Coder eksekusi
                  │
                  ▼
    PRD-2 ──► Coder eksekusi    ◄── Analyst siapkan PRD-3 (paralel)
                  │
                  ▼
    PRD-3 ──► Coder eksekusi    ◄── Analyst siapkan PRD-4 (paralel)
                  │
                  ▼
    PRD-4 ──► Coder eksekusi
                  │
                  ▼
               DONE

---

## Ringkasan Fitur Per Fase

PRD-1 — Fondasi
  - Setup proyek Laravel + Docker (PHP-FPM + Nginx + Node)
  - Auth: login/logout single user, seed akun default
  - Agent: tambah, lihat, edit, hapus (soft delete), paginasi

PRD-2 — Input Data
  - Token Usage: catat pemakaian token per agent (input/output/cost/tanggal)
  - Token Usage: filter per agent dan rentang tanggal
  - Provider Balance: catat saldo per provider, tampil saldo terbaru

PRD-3 — Dashboard
  - 4 summary cards: total agent, total token, total cost, active providers
  - Filter periode: week / month / year / all time
  - Tabel: Top 5 Agents, 10 Recent Usage, Provider Balance summary
  - Navigasi cepat ke detail dari dashboard

PRD-4 — Visual & Export
  - Line chart: tren token per hari
  - Bar chart: perbandingan token per agent
  - Doughnut chart: distribusi cost per agent
  - Export CSV: Token Usage (ikut filter) + Provider Balance (semua entri)

---

## Dependency Eksternal

| Package      | Versi  | Dipakai Di |
|--------------|--------|------------|
| chart.js     | ^4.x   | PRD-4      |
| vue-chartjs  | ^5.x   | PRD-4      |

Semua fase lain tidak menambah dependency di luar Laravel Breeze default.

---

## Open Questions Terbuka (perlu dijawab sebelum eksekusi fase terkait)

PRD-2:
  OQ-01: Provider field — dropdown tetap atau free-text?
  OQ-02: Currency di token_usages — USD-only atau multi-currency?
  OQ-03: Filter Token Usage — state di URL params atau lokal Vue?

PRD-3:
  OQ-01: Breakdown cost per provider di cards, atau total saja?
  OQ-02: "This Week" mulai Senin (ISO) atau Minggu?

PRD-4:
  OQ-01: Chart support dark/light theme?
  OQ-02: Tombol Export CSV di halaman Dashboard juga?
