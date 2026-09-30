# PRD-2 — Token Management: Token Usage & Provider Balance

Versi: 1.0
Tanggal: 30 September 2026
Status: Draft
Depends on: PRD-1 (Agent Management harus sudah selesai)

---

## Overview

PRD-2 menambahkan dua modul inti:
1. Token Usage — riwayat pemakaian token per agent, diinput manual
2. Provider Balance — saldo/kredit tersisa per provider, diinput manual

Semua data diinput manual oleh Owner. Tidak ada kalkulasi otomatis dari API eksternal.
Cost per entry bisa diisi manual atau dibiarkan kosong bila tidak ingin tracking biaya.

---

## Scope PRD-2

In-scope:
- Modul Token Usage: Create, Read, Update, Delete
- Modul Provider Balance: Create, Read, Update, Delete
- Relasi Token Usage → Agent (dari PRD-1)
- Filter dan sort tabel Token Usage (per agent, per tanggal)

Out-of-scope (dikerjakan di PRD berikutnya):
- Dashboard statistik / chart agregasi
- Kalkulasi cost otomatis berdasarkan harga per token
- Export data ke CSV / Excel

---

## Actors & Use Cases

Actor: Owner (satu-satunya user, sudah login)

**UC-01 Catat Token Usage**
- Trigger : Owner selesai menggunakan agent dan ingin mencatat pemakaian
- Flow    : Klik "Add Usage" → pilih agent, isi model, input tokens, output tokens,
           cost (opsional), tanggal, catatan → submit
- Outcome : Entry usage baru muncul di tabel riwayat

**UC-02 Lihat Riwayat Token Usage**
- Trigger : Owner klik menu "Token Usage"
- Flow    : Sistem tampilkan tabel semua entry usage, bisa filter per agent / per rentang tanggal
- Outcome : Tabel berisi: tanggal, agent name, model, input tokens, output tokens, cost, catatan

**UC-03 Edit Token Usage**
- Trigger : Owner klik ikon edit pada row usage
- Flow    : Form terisi data lama → ubah → submit
- Outcome : Data entry terupdate

**UC-04 Hapus Token Usage**
- Trigger : Owner klik ikon hapus pada row usage
- Flow    : Konfirmasi dialog → confirm → hapus
- Outcome : Entry hilang dari tabel (soft delete)

**UC-05 Catat / Update Provider Balance**
- Trigger : Owner top-up atau ingin mencatat saldo terkini suatu provider
- Flow    : Klik "Add Balance" atau edit entri yang ada → isi provider, nominal, mata uang,
           tanggal update, catatan → submit
- Outcome : Saldo provider tersimpan / terupdate di tabel

**UC-06 Lihat Daftar Provider Balance**
- Trigger : Owner klik menu "Balances"
- Flow    : Sistem tampilkan tabel semua provider beserta saldo terakhir
- Outcome : Tabel berisi: provider, balance, currency, last updated, catatan

**UC-07 Hapus Provider Balance**
- Trigger : Owner klik ikon hapus pada row balance
- Flow    : Konfirmasi dialog → confirm → hapus
- Outcome : Entry balance hilang dari tabel (soft delete)

---

## Data Model

### Tabel: token_usages

| Kolom        | Tipe           | Keterangan                                       |
|--------------|----------------|--------------------------------------------------|
| id           | BIGINT PK      | Auto increment                                   |
| agent_id     | BIGINT FK      | Relasi ke agents.id (nullable: agent bisa dihapus) |
| model        | VARCHAR(100)   | Model yang dipakai, e.g. "gpt-4o"                |
| input_tokens | INT UNSIGNED   | Jumlah token input                               |
| output_tokens| INT UNSIGNED   | Jumlah token output                              |
| cost         | DECIMAL(10,6)  | Nullable — biaya dalam USD, diisi manual         |
| used_at      | DATE           | Tanggal pemakaian (bukan created_at)             |
| notes        | TEXT           | Nullable — catatan bebas                         |
| deleted_at   | TIMESTAMP      | Nullable, soft delete                            |
| created_at   | TIMESTAMP      |                                                  |
| updated_at   | TIMESTAMP      |                                                  |

Relasi:
- token_usages.agent_id → agents.id (FK, ON DELETE SET NULL)
- Agent yang di-soft delete tetap bisa dilihat dari entry usage (agent_id tidak hilang)

### Tabel: provider_balances

| Kolom           | Tipe          | Keterangan                                    |
|-----------------|---------------|-----------------------------------------------|
| id              | BIGINT PK     | Auto increment                                |
| provider        | VARCHAR(100)  | Nama provider, e.g. "OpenAI", "Anthropic"     |
| balance         | DECIMAL(10,2) | Nominal saldo                                 |
| currency        | VARCHAR(10)   | Default 'USD', bisa 'IDR' dll                 |
| last_updated_at | DATE          | Tanggal saldo ini dicatat / diupdate          |
| notes           | TEXT          | Nullable — catatan bebas                      |
| deleted_at      | TIMESTAMP     | Nullable, soft delete                         |
| created_at      | TIMESTAMP     |                                               |
| updated_at      | TIMESTAMP     |                                               |

Catatan: Satu provider bisa punya lebih dari satu entri (riwayat saldo).
Tampilan di UI cukup menampilkan entri terbaru per provider berdasarkan last_updated_at.

---

## API / Route Contract

Semua route di bawah middleware `auth`.

### Token Usage

| Method | URI                       | Keterangan                                                            |
|--------|---------------------------|-----------------------------------------------------------------------|
| GET    | /token-usages             | Render Inertia page TokenUsages/Index, props: { usages: paginated, agents: list } |
| POST   | /token-usages             | Store entry baru, redirect ke /token-usages + flash                   |
| GET    | /token-usages/{id}/edit   | Render Inertia page TokenUsages/Form, props: { usage, agents: list }  |
| PUT    | /token-usages/{id}        | Update entry, redirect ke /token-usages + flash                       |
| DELETE | /token-usages/{id}        | Soft delete, redirect ke /token-usages + flash                        |

Query params untuk filter (GET /token-usages):
- agent_id  : filter per agent (opsional)
- date_from : filter tanggal mulai (opsional, format: Y-m-d)
- date_to   : filter tanggal akhir (opsional, format: Y-m-d)

### Provider Balance

| Method | URI                          | Keterangan                                                                  |
|--------|------------------------------|-----------------------------------------------------------------------------|
| GET    | /provider-balances           | Render Inertia page ProviderBalances/Index, props: { balances: paginated }  |
| POST   | /provider-balances           | Store entry baru, redirect ke /provider-balances + flash                    |
| GET    | /provider-balances/{id}/edit | Render Inertia page ProviderBalances/Form, props: { balance }               |
| PUT    | /provider-balances/{id}      | Update entry, redirect ke /provider-balances + flash                        |
| DELETE | /provider-balances/{id}      | Soft delete, redirect ke /provider-balances + flash                         |

---

## UI Components (Vue)

**Sidebar (update AppLayout.vue dari PRD-1)**
- Tambah nav item: "Token Usage" → /token-usages
- Tambah nav item: "Balances" → /provider-balances

**Pages/TokenUsages/Index.vue**
- Tabel: Date, Agent Name, Model, Input Tokens, Output Tokens, Cost, Notes, Actions
- Filter bar: dropdown Agent + date range picker (date_from, date_to)
- Tombol "Add Usage"
- Cost yang null ditampilkan sebagai "—"
- Paginasi 15 row per halaman

**Pages/TokenUsages/Form.vue** (Create & Edit)
- Field:
  - Agent (dropdown dari daftar agent active, required)
  - Model (text input, required) — pre-fill dengan model_default agent yang dipilih
  - Input Tokens (number, required, min 0)
  - Output Tokens (number, required, min 0)
  - Cost in USD (number decimal, opsional)
  - Date (date picker, required, default: hari ini)
  - Notes (textarea, opsional)
- Tombol Save + Cancel

**Pages/ProviderBalances/Index.vue**
- Tabel: Provider, Balance, Currency, Last Updated, Notes, Actions
- Tampilkan hanya entri terbaru per provider (grouped by provider, latest last_updated_at)
- Tombol "Add Balance"
- Paginasi 15 row per halaman

**Pages/ProviderBalances/Form.vue** (Create & Edit)
- Field:
  - Provider (text input, required)
  - Balance (number decimal, required)
  - Currency (text input, required, default: USD)
  - Last Updated At (date picker, required, default: hari ini)
  - Notes (textarea, opsional)
- Tombol Save + Cancel

---

## Acceptance Criteria

- AC-01: Entry token usage tidak bisa disimpan tanpa agent, model, input_tokens, output_tokens, dan used_at.
- AC-02: input_tokens dan output_tokens hanya menerima angka bulat >= 0.
- AC-03: cost boleh kosong; jika diisi harus berupa angka desimal >= 0.
- AC-04: Saat user memilih agent di form, field model otomatis ter-prefill dengan model_default agent tersebut (bisa diubah manual).
- AC-05: Filter per agent dan rentang tanggal bekerja secara kombinasi (AND, bukan OR).
- AC-06: Entry provider balance tidak bisa disimpan tanpa provider, balance, currency, dan last_updated_at.
- AC-07: Halaman Index Provider Balance hanya menampilkan entri terbaru per provider berdasarkan last_updated_at.
- AC-08: Hapus pada kedua modul adalah soft delete.
- AC-09: Flash message muncul setelah setiap operasi create/update/delete.
- AC-10: Agent yang sudah di-soft delete tetap tampil di dropdown form Token Usage (label: "AgentName (inactive)").

---

## Assumptions & Open Questions

**Assumptions:**
- A1: Cost selalu dalam USD bila diisi; field currency tidak ada di token_usages (hanya di provider_balances).
- A2: Tidak ada kalkulasi otomatis cost dari jumlah token — semua manual.
- A3: Satu provider bisa punya banyak entri balance (history), tapi Index hanya tampilkan yang terbaru.
- A4: "Total tokens" per entry = input_tokens + output_tokens — tidak disimpan di DB, dihitung di frontend bila perlu.

**Open Questions:**
- OQ-01: Apakah perlu kolom "total_cost" agregat per agent yang muncul di halaman Agent Index (PRD-1)?
         Kalau iya, ini akan diimplementasi di PRD-3 (Dashboard) bukan di sini.
- OQ-02: Apakah currency di token_usages perlu ditambahkan, atau USD-only sudah cukup?
- OQ-03: Apakah filter di Token Usage Index perlu disimpan ke URL params (supaya bisa di-bookmark/share),
         atau cukup state lokal Vue yang reset saat navigasi?
