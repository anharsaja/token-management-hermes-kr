# PRD-4 — Token Management: Charts & Data Export

Versi: 1.0
Tanggal: 30 September 2026
Status: Draft
Depends on: PRD-1, PRD-2, PRD-3 (Dashboard harus sudah jalan)

---

## Overview

PRD-4 menambahkan visualisasi chart ke halaman Dashboard yang sudah ada di PRD-3,
dan fitur export data ke CSV untuk Token Usage dan Provider Balance.

Chart memberikan gambaran tren pemakaian token secara visual.
Export memungkinkan Owner menyimpan data ke luar aplikasi untuk keperluan analisis mandiri.

Tidak ada tabel baru. Chart library yang dipakai: Chart.js via vue-chartjs
(lightweight, zero-dependency eksternal besar, kompatibel penuh dengan Vue 3).

---

## Scope PRD-4

In-scope:
- 3 chart di halaman Dashboard:
  1. Line chart: tren total token per hari dalam periode aktif
  2. Bar chart: perbandingan total token per agent dalam periode aktif
  3. Doughnut chart: distribusi cost per agent dalam periode aktif
- Chart mengikuti filter periode yang sudah ada di PRD-3
- Export CSV: Token Usage (dengan filter aktif)
- Export CSV: Provider Balance (semua entri, bukan hanya terbaru)

Out-of-scope:
- Chart interaktif real-time (WebSocket, polling)
- Export ke format Excel (.xlsx) atau PDF
- Chart di halaman selain Dashboard
- Notifikasi saldo menipis (bisa masuk PRD-5 bila diperlukan)
- Perbandingan antar periode (month-over-month overlay)

---

## Actors & Use Cases

Actor: Owner (sudah login)

**UC-01 Lihat Chart Tren Token**
- Trigger : Owner buka Dashboard (chart muncul otomatis di bawah summary cards)
- Flow    : Sistem render line chart token usage per hari sesuai periode aktif
- Outcome : Owner melihat tren naik/turun pemakaian token dalam periode

**UC-02 Lihat Chart Perbandingan Agent**
- Trigger : Owner scroll ke section chart di Dashboard
- Flow    : Sistem render bar chart total token per agent dalam periode aktif
- Outcome : Owner tahu agent mana yang paling banyak memakai token

**UC-03 Lihat Chart Distribusi Cost**
- Trigger : Owner scroll ke section chart di Dashboard
- Flow    : Sistem render doughnut chart distribusi cost per agent
- Outcome : Owner tahu proporsi biaya masing-masing agent
- Catatan : Jika semua cost NULL, chart tampilkan pesan "No cost data available"

**UC-04 Export CSV Token Usage**
- Trigger : Owner klik tombol "Export CSV" di halaman Token Usage Index
- Flow    : Browser download file CSV dengan data sesuai filter yang sedang aktif
- Outcome : File CSV terdownload ke komputer Owner

**UC-05 Export CSV Provider Balance**
- Trigger : Owner klik tombol "Export CSV" di halaman Provider Balance Index
- Flow    : Browser download file CSV semua entri balance (termasuk history)
- Outcome : File CSV terdownload ke komputer Owner

---

## Data untuk Chart

### Chart 1: Line Chart — Token Trend

Sumber query:
  SELECT used_at, SUM(input_tokens + output_tokens) as total_tokens
  FROM token_usages
  WHERE used_at BETWEEN {start} AND {end}
    AND deleted_at IS NULL
  GROUP BY used_at
  ORDER BY used_at ASC

Output ke Vue:
  token_trend: [
    { date: 'Y-m-d', total_tokens: int },
    ...
  ]

Catatan:
- Tanggal tanpa data tetap muncul di chart dengan nilai 0 (fill gap)
- Sumbu X: tanggal, Sumbu Y: total tokens
- Jika periode "all time" dan data > 365 hari, group by bulan (bukan hari)

### Chart 2: Bar Chart — Token per Agent

Sumber query:
  SELECT agent_id, agents.name, SUM(input_tokens + output_tokens) as total_tokens
  FROM token_usages
  LEFT JOIN agents ON token_usages.agent_id = agents.id
  WHERE used_at BETWEEN {start} AND {end}
    AND token_usages.deleted_at IS NULL
  GROUP BY agent_id
  ORDER BY total_tokens DESC
  LIMIT 10

Output ke Vue:
  token_per_agent: [
    { agent_name: string, total_tokens: int, is_deleted: bool },
    ...
  ]

### Chart 3: Doughnut Chart — Cost per Agent

Sumber query:
  SELECT agent_id, agents.name, SUM(cost) as total_cost
  FROM token_usages
  LEFT JOIN agents ON token_usages.agent_id = agents.id
  WHERE used_at BETWEEN {start} AND {end}
    AND token_usages.deleted_at IS NULL
    AND cost IS NOT NULL
  GROUP BY agent_id
  ORDER BY total_cost DESC

Output ke Vue:
  cost_per_agent: [
    { agent_name: string, total_cost: float },
    ...
  ]

---

## API / Route Contract

Semua route di bawah middleware `auth`.

### Update: GET /dashboard

Props tambahan yang dikirim ke Dashboard.vue (extend dari PRD-3):

```
{
  // ... props PRD-3 tetap ada ...

  token_trend: [
    { date: string, total_tokens: int },
    ...
  ],
  token_per_agent: [
    { agent_name: string, total_tokens: int, is_deleted: bool },
    ...
  ],
  cost_per_agent: [
    { agent_name: string, total_cost: float },
    ...
  ]
}
```

### Export Endpoints

| Method | URI                          | Keterangan                                              |
|--------|------------------------------|---------------------------------------------------------|
| GET    | /token-usages/export         | Download CSV token usage sesuai filter aktif            |
| GET    | /provider-balances/export    | Download CSV semua entri provider balance               |

Query params GET /token-usages/export (sama dengan filter di Index):
- agent_id  : opsional
- date_from : opsional
- date_to   : opsional

Response kedua endpoint:
- Content-Type: text/csv
- Content-Disposition: attachment; filename="token-usages-{date}.csv"
- Return langsung dari controller, bukan Inertia::render()

### CSV Format: Token Usage

```
Date,Agent,Model,Input Tokens,Output Tokens,Total Tokens,Cost (USD),Notes
2026-09-01,Kiro Dev Assistant,gpt-4o,1200,800,2000,0.005000,test session
2026-09-02,Claude Assistant,claude-3-5-sonnet,500,300,800,,
```

### CSV Format: Provider Balance

```
Provider,Balance,Currency,Last Updated,Notes
OpenAI,25.50,USD,2026-09-30,top up
Anthropic,10.00,USD,2026-09-28,
```

---

## UI Components (Vue)

### Update: Pages/Dashboard.vue

Tambahkan section chart di antara summary cards dan tabel-tabel PRD-3:

```
[Summary Cards x4]          ← PRD-3
[Charts Row]                ← PRD-4 baru
  [Line Chart 8/12] [Doughnut 4/12]
[Bar Chart full width]      ← PRD-4 baru
[Top Agents Table]          ← PRD-3
[Recent Usage Table]        ← PRD-3
[Provider Balance Table]    ← PRD-3
```

### Components/Dashboard/TokenTrendChart.vue

- Library : vue-chartjs (Chart.js)
- Tipe    : Line chart
- Props   : token_trend: Array, period: string
- X-axis  : tanggal (format: "DD MMM" untuk week/month, "MMM YYYY" untuk year/all)
- Y-axis  : total tokens
- Tooltip : "{date}: {N} tokens"
- Tanggal kosong di-fill nilai 0 (computed di Vue sebelum masuk Chart.js)
- Warna   : satu warna solid (indigo/biru)

### Components/Dashboard/TokenPerAgentChart.vue

- Library : vue-chartjs (Chart.js)
- Tipe    : Horizontal bar chart
- Props   : token_per_agent: Array
- X-axis  : total tokens
- Y-axis  : nama agent
- Agent deleted diberi suffix " (deleted)" di label
- Max 10 bar
- Warna   : palette warna berbeda per agent (preset array warna)

### Components/Dashboard/CostPerAgentChart.vue

- Library : vue-chartjs (Chart.js)
- Tipe    : Doughnut chart
- Props   : cost_per_agent: Array
- Legend  : di bawah chart
- Tooltip : "{agent}: ${cost}"
- Jika cost_per_agent kosong: tampilkan teks "No cost data for this period"
- Warna   : palette warna preset, sama dengan bar chart

### Update: Pages/TokenUsages/Index.vue

- Tambah tombol "Export CSV" di sebelah tombol "Add Usage"
- Klik → trigger Inertia visit ke /token-usages/export dengan query params filter aktif
- Download otomatis via browser (server kirim response CSV dengan header attachment)

### Update: Pages/ProviderBalances/Index.vue

- Tambah tombol "Export CSV" di sebelah tombol "Add Balance"
- Klik → trigger visit ke /provider-balances/export

---

## Dependency Baru

| Package       | Versi   | Alasan                                    |
|---------------|---------|-------------------------------------------|
| chart.js      | ^4.x    | Core charting library                     |
| vue-chartjs   | ^5.x    | Vue 3 wrapper untuk Chart.js              |

Install: `npm install chart.js vue-chartjs`

Tidak ada dependency PHP baru — export CSV menggunakan built-in PHP fputcsv().

---

## Acceptance Criteria

- AC-01: Line chart tampil di Dashboard sesuai periode aktif; saat filter periode diubah, chart terupdate.
- AC-02: Bar chart menampilkan maksimal 10 agent diurutkan dari total tokens tertinggi.
- AC-03: Doughnut chart hanya tampil jika ada minimal satu entry cost yang tidak NULL dalam periode; jika tidak ada, tampilkan pesan "No cost data for this period".
- AC-04: Semua tanggal dalam periode line chart ter-representasi (gap hari tanpa data = 0, bukan dilewati).
- AC-05: Tombol "Export CSV" di Token Usage Index menghasilkan file CSV yang terdownload otomatis.
- AC-06: CSV Token Usage yang diexport mengikuti filter agent_id dan date range yang sedang aktif di Index.
- AC-07: Tombol "Export CSV" di Provider Balance Index menghasilkan file CSV semua entri (bukan hanya terbaru per provider).
- AC-08: Filename CSV mengikuti format: token-usages-YYYY-MM-DD.csv dan provider-balances-YYYY-MM-DD.csv.
- AC-09: Jika periode "all time" dan rentang data > 365 hari, line chart di-group per bulan bukan per hari.
- AC-10: Chart tidak crash bila tidak ada data — tampil kosong dengan label yang jelas.

---

## Assumptions & Open Questions

**Assumptions:**
- A1: Chart.js + vue-chartjs cukup untuk kebutuhan visual personal tool ini — tidak perlu library berat seperti ECharts atau D3.
- A2: Fill gap tanggal kosong ke nilai 0 dilakukan di Vue (computed property), bukan di query SQL.
- A3: Export CSV tidak perlu konfirmasi dialog — langsung download saat tombol diklik.
- A4: Tidak ada pagination di CSV export — semua data yang match filter diexport sekaligus.
- A5: Cost di CSV diformat sebagai angka desimal plain (0.005000), bukan string currency.

**Open Questions:**
- OQ-01: Apakah warna chart perlu mengikuti tema dark/light, atau cukup satu tema saja?
- OQ-02: Apakah perlu tombol "Export CSV" juga di halaman Dashboard langsung
         (bukan hanya di Index masing-masing modul)?
- OQ-03: Apakah PRD-5 masih diperlukan, atau PRD-4 ini sudah menjadi fitur terakhir project?
