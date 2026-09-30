# Architecture Overview — Token Management

Versi: 1.0
Tanggal: 30 September 2026

---

## Gambaran Umum

Token Management adalah aplikasi monolitik berbasis Laravel + Inertia.js + Vue 3.
Tidak ada pemisahan backend API dan frontend — Inertia bertindak sebagai jembatan
yang mengirim props langsung dari controller ke Vue component tanpa REST API terpisah.

```
Browser
  |
  | HTTP Request
  v
Laravel Router
  |
  v
Middleware (auth, etc.)
  |
  v
Controller
  |-- Form Request (validasi)
  |-- Model / Eloquent
  |
  v
Inertia::render('PageName', ['props' => $data])
  |
  v
Vue Component (menerima props dari server)
  |
  v
Tailwind CSS (styling)
```

---

## Komponen Sistem

### Backend (Laravel)

- Routes       : routes/web.php — semua route Inertia
- Controllers  : app/Http/Controllers/
- Form Requests: app/Http/Requests/
- Models       : app/Models/ — Eloquent dengan soft delete
- Migrations   : database/migrations/
- Seeders      : database/seeders/

### Frontend (Vue + Inertia)

- Pages        : resources/js/Pages/ — satu file per halaman Inertia
- Components   : resources/js/Components/ — komponen reusable
- Layouts      : resources/js/Layouts/ — AppLayout.vue
- Composables  : resources/js/composables/ — logic reusable (useForm, dll)

### Database

- Engine : SQLite (file: database/database.sqlite)
- ORM    : Eloquent

---

## Modul yang Direncanakan

| Modul              | PRD   | Status      |
|--------------------|-------|-------------|
| Auth + Foundation  | PRD-1 | Planned     |
| Agent Management   | PRD-1 | Planned     |
| Token Usage        | PRD-2 | Planned     |
| Provider Balance   | PRD-2 | Planned     |
| Dashboard & Stats  | PRD-3 | Planned     |
| Charts & Export    | PRD-4 | Planned     |

---

## Data Flow: Contoh Create Agent

1. User klik "Add Agent" → Vue navigasi ke /agents/create via Inertia Link
2. Laravel render Inertia page Agents/Form.vue (mode: create)
3. User isi form → submit via useForm().post('/agents')
4. Laravel terima POST /agents → AgentRequest validasi → Agent::create()
5. Redirect ke /agents dengan flash message
6. Inertia intercept redirect → render Agents/Index.vue dengan data baru
