# PRD-3 — Token Management: Dashboard & Statistics

Versi: 1.0
Tanggal: 30 September 2026
Status: Draft
Depends on: PRD-1 (Agent), PRD-2 (Token Usage, Provider Balance)

---

## Overview

PRD-3 mengisi halaman Dashboard yang di PRD-1 hanya berupa placeholder.
Tujuannya: memberikan ringkasan visual pemakaian token dan saldo provider
dalam satu halaman — tanpa navigasi ke halaman lain.

Semua data diambil dari tabel yang sudah ada (agents, token_usages, provider_balances).
Tidak ada tabel baru. Tidak ada kalkulasi real-time dari API eksternal.

---

## Scope PRD-3

In-scope:
- Summary cards: total agent, total token dipakai (all-time), total cost (all-time), jumlah provider aktif
- Tabel "Top Agents by Token Usage" — N agent dengan pemakaian token tertinggi
- Tabel "Recent Token Usage" — 10 entry usage terbaru
- Tabel "Provider Balance Summary" — saldo terbaru per provider
- Filter periode global di Dashboard (this week / this month / this year / all time)

Out-of-scope:
- Chart / grafik visual (line chart, bar chart) — bisa masuk PRD-4 bila diperlukan
- Export data
- Notifikasi saldo menipis
- Perbandingan antar periode (month-over-month)

---

## Actors & Use Cases

Actor: Owner (sudah login)

**UC-01 Lihat Ringkasan Dashboard**
- Trigger : Owner buka / klik menu "Dashboard"
- Flow    : Sistem tampilkan summary cards + tabel-tabel ringkasan
- Outcome : Owner melihat gambaran umum pemakaian token dan saldo

**UC-02 Filter Periode**
- Trigger : Owner pilih periode dari dropdown filter (this week / this month / this year / all time)
- Flow    : Dashboard reload data sesuai periode yang dipilih
- Outcome : Semua card dan tabel terupdate sesuai filter (kecuali Provider Balance yang selalu tampil saldo terbaru)

**UC-03 Navigasi Cepat ke Detail**
- Trigger : Owner klik nama agent di tabel Top Agents atau klik "View All" di tabel Recent Usage
- Flow    : Navigasi ke halaman Token Usage Index dengan filter agent pre-applied
- Outcome : Halaman Token Usage terbuka dengan data yang relevan

---

## Data yang Ditampilkan

### Summary Cards (4 card)

| Card                   | Nilai                                                              | Catatan                          |
|------------------------|--------------------------------------------------------------------|----------------------------------|
| Total Agents           | COUNT agents WHERE status='active' AND deleted_at IS NULL          | Tidak terpengaruh filter periode |
| Total Tokens Used      | SUM(input_tokens + output_tokens) dari token_usages dalam periode  | Terpengaruh filter periode       |
| Total Cost             | SUM(cost) dari token_usages dalam periode (NULL dianggap 0)        | Terpengaruh filter periode, USD  |
| Active Providers       | COUNT DISTINCT provider dari provider_balances WHERE deleted_at IS NULL | Tidak terpengaruh filter periode |

### Tabel: Top Agents by Token Usage

Kolom: Rank, Agent Name, Model Default, Total Tokens, Total Cost, Entry Count
Sumber: token_usages GROUP BY agent_id, JOIN agents
Limit: 5 agent teratas berdasarkan total tokens dalam periode
Catatan: Agent yang di-soft delete tetap muncul bila punya usage, label "(deleted)"

### Tabel: Recent Token Usage

Kolom: Date, Agent Name, Model, Input Tokens, Output Tokens, Cost
Sumber: token_usages ORDER BY used_at DESC, created_at DESC
Limit: 10 entry terbaru (tidak terpengaruh filter periode — selalu 10 terbaru all-time)

### Tabel: Provider Balance Summary

Kolom: Provider, Balance, Currency, Last Updated
Sumber: provider_balances — entri terbaru per provider (MAX last_updated_at)
Tidak terpengaruh filter periode — selalu tampil saldo terkini

---

## API / Route Contract

Semua route di bawah middleware `auth`.

| Method | URI         | Keterangan                                                   |
|--------|-------------|--------------------------------------------------------------|
| GET    | /dashboard  | Render Inertia page Dashboard, props: lihat di bawah         |

Query params:
- period : 'week' | 'month' | 'year' | 'all' (default: 'month')

Props yang dikirim controller ke Dashboard.vue:

```
{
  period: 'month',                  // periode aktif
  cards: {
    total_agents: int,
    total_tokens: int,
    total_cost: float,
    active_providers: int
  },
  top_agents: [
    {
      agent_id: int|null,
      agent_name: string,
      model_default: string,
      total_tokens: int,
      total_cost: float|null,
      entry_count: int,
      is_deleted: bool
    },
    ...                             // max 5
  ],
  recent_usages: [
    {
      used_at: string,              // Y-m-d
      agent_name: string,
      model: string,
      input_tokens: int,
      output_tokens: int,
      cost: float|null
    },
    ...                             // max 10
  ],
  provider_balances: [
    {
      provider: string,
      balance: float,
      currency: string,
      last_updated_at: string       // Y-m-d
    },
    ...
  ]
}
```

Semua agregasi dilakukan di DashboardController — tidak ada query di Vue.

---

## UI Components (Vue)

**Pages/Dashboard.vue**
- Layout: filter periode di kanan atas → 4 summary cards → 3 tabel berdampingan (atau stack di mobile)
- Filter periode: segmented button / select — week / month / year / all time
- Saat filter berubah: Inertia router.get('/dashboard', { period }) — bukan fetch/axios

**Components/Dashboard/SummaryCard.vue**
- Props: title, value, subtitle (opsional)
- Tampil 4 card dalam satu baris (grid 4 kolom)
- Total Cost diformat: "$0.001234" (6 desimal bila < 1, 2 desimal bila >= 1)

**Components/Dashboard/TopAgentsTable.vue**
- Kolom: #, Agent, Model Default, Total Tokens, Total Cost, Entries
- Agent deleted diberi label badge "deleted" abu-abu
- Total Cost NULL ditampilkan "—"
- Klik agent name → navigate ke /token-usages?agent_id={id}

**Components/Dashboard/RecentUsageTable.vue**
- Kolom: Date, Agent, Model, Input, Output, Cost
- "View All" link di pojok kanan atas → /token-usages

**Components/Dashboard/ProviderBalanceTable.vue**
- Kolom: Provider, Balance, Currency, Last Updated
- Balance diformat sesuai currency (USD: 2 desimal, lainnya: 2 desimal)

---

## Acceptance Criteria

- AC-01: Dashboard hanya bisa diakses setelah login.
- AC-02: Default periode saat pertama buka adalah "This Month".
- AC-03: Filter periode mengubah nilai summary cards Total Tokens dan Total Cost; Total Agents dan Active Providers tidak berubah.
- AC-04: Tabel Top Agents menampilkan maksimal 5 agent diurutkan dari total tokens tertinggi dalam periode aktif.
- AC-05: Tabel Recent Usage selalu menampilkan 10 entry terbaru tanpa memperhatikan filter periode.
- AC-06: Tabel Provider Balance selalu menampilkan saldo terbaru per provider tanpa memperhatikan filter periode.
- AC-07: Jika belum ada data usage sama sekali, Dashboard tetap tampil dengan nilai card 0 dan tabel kosong (bukan error/blank page).
- AC-08: Agent yang sudah dihapus (soft delete) tetap muncul di Top Agents bila punya data usage, diberi label "(deleted)".
- AC-09: Klik nama agent di Top Agents membuka /token-usages dengan filter agent_id pre-applied.
- AC-10: Semua agregasi dihitung di server (DashboardController), bukan di Vue.

---

## Assumptions & Open Questions

**Assumptions:**
- A1: Tidak ada tabel baru di PRD-3 — semua data dari tabel yang sudah ada.
- A2: "This Month" = dari tanggal 1 bulan berjalan sampai hari ini.
- A3: "This Week" = Senin sampai hari ini (ISO week).
- A4: Semua query agregasi cukup performant di SQLite untuk skala personal tool (ratusan ribu baris).
- A5: Tidak ada caching khusus — query langsung ke DB setiap load Dashboard.
- A6: Total Cost yang NULL (tidak diisi) dianggap 0 dalam agregasi SUM.

**Open Questions:**
- OQ-01: Apakah perlu ditampilkan breakdown cost per provider di summary cards,
         atau cukup total cost keseluruhan?
- OQ-02: Apakah "This Week" mulai dari Senin (ISO) atau Minggu (US week)?
- OQ-03: Apakah PRD-4 diperlukan untuk chart visual, atau PRD-3 ini sudah cukup
         sebagai dashboard final?
