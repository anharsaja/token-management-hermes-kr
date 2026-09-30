<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\TokenUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class UsageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $range = in_array($request->get('range'), ['today', '7d', '30d', '60d', 'all'])
            ? $request->get('range') : '7d';
        $tab = in_array($request->get('tab'), ['overview', 'details'])
            ? $request->get('tab') : 'overview';

        [$dateFrom, $dateTo] = $this->rangeToDate($range);

        // ── KPI cards ────────────────────────────────────────────────────
        $baseQuery = TokenUsage::whereNull('deleted_at');
        if ($dateFrom) {
            $baseQuery->whereDate('used_at', '>=', $dateFrom)
                      ->whereDate('used_at', '<=', $dateTo);
        }

        $kpiRaw = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_requests,
                COALESCE(SUM(input_tokens), 0) as total_input,
                COALESCE(SUM(output_tokens), 0) as total_output,
                COALESCE(SUM(input_tokens + output_tokens), 0) as total_tokens,
                COALESCE(SUM(cost), 0) as est_cost
            ')
            ->first();

        $kpi = [
            'total_requests'      => (int) $kpiRaw->total_requests,
            'total_input_tokens'  => (int) $kpiRaw->total_input,
            'total_output_tokens' => (int) $kpiRaw->total_output,
            'total_tokens'        => (int) $kpiRaw->total_tokens,
            'est_cost'            => (float) $kpiRaw->est_cost,
        ];

        // ── Node graph ───────────────────────────────────────────────────
        $usagePerAgent = (clone $baseQuery)
            ->selectRaw('agent_id, SUM(input_tokens + output_tokens) as total_tokens, SUM(cost) as total_cost')
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        $nodes = Agent::withTrashed()
            ->orderBy('name')
            ->get()
            ->map(function ($agent) use ($usagePerAgent) {
                $u = $usagePerAgent->get($agent->id);
                return [
                    'id'            => $agent->id,
                    'name'          => $agent->name,
                    'provider'      => $agent->provider,
                    'model_default' => $agent->model_default,
                    'total_tokens'  => $u ? (int) $u->total_tokens : 0,
                    'total_cost'    => $u ? (float) $u->total_cost : null,
                    'is_active'     => $agent->status === 'active' && $agent->deleted_at === null,
                    'has_usage'     => $u !== null,
                ];
            });

        // ── Recent log — 10 latest all-time ─────────────────────────────
        $recentLog = TokenUsage::whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['model', 'input_tokens', 'output_tokens', 'created_at'])
            ->map(fn ($u) => [
                'model'         => $u->model,
                'input_tokens'  => $u->input_tokens,
                'output_tokens' => $u->output_tokens,
                'created_at'    => $u->created_at?->toISOString(),
            ]);

        // ── Chart data — fill gaps ────────────────────────────────────────
        $chart = $this->buildChartData(clone $baseQuery, $dateFrom, $dateTo, $range);

        // ── Details tab ──────────────────────────────────────────────────
        $usages  = null;
        $agents  = null;
        $filters = null;

        if ($tab === 'details') {
            $sortBy  = in_array($request->get('sort_by'), ['used_at', 'total_tokens', 'cost']) ? $request->get('sort_by') : 'used_at';
            $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

            $detailQuery = (clone $baseQuery)->with('agent');

            if ($request->filled('agent_id')) {
                $detailQuery->where('agent_id', $request->agent_id);
            }
            if ($request->filled('date_from')) {
                $detailQuery->whereDate('used_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $detailQuery->whereDate('used_at', '<=', $request->date_to);
            }

            if ($sortBy === 'total_tokens') {
                $detailQuery->orderByRaw("(input_tokens + output_tokens) {$sortDir}");
            } else {
                $detailQuery->orderBy($sortBy, $sortDir);
            }

            $usages  = $detailQuery->paginate(20)->withQueryString();
            $agents  = Agent::withTrashed()->orderBy('name')->get(['id', 'name', 'deleted_at']);
            $filters = $request->only(['agent_id', 'date_from', 'date_to', 'sort_by', 'sort_dir']);
        }

        return Inertia::render('Usage/Index', [
            'range'      => $range,
            'tab'        => $tab,
            'kpi'        => $kpi,
            'nodes'      => $nodes,
            'recent_log' => $recentLog,
            'chart'      => $chart,
            'usages'     => $usages,
            'agents'     => $agents,
            'filters'    => $filters,
        ]);
    }

    private function rangeToDate(string $range): array
    {
        $today = Carbon::today();
        return match ($range) {
            'today' => [$today->format('Y-m-d'), $today->format('Y-m-d')],
            '7d'    => [$today->copy()->subDays(6)->format('Y-m-d'), $today->format('Y-m-d')],
            '30d'   => [$today->copy()->subDays(29)->format('Y-m-d'), $today->format('Y-m-d')],
            '60d'   => [$today->copy()->subDays(59)->format('Y-m-d'), $today->format('Y-m-d')],
            default => [null, null],
        };
    }

    private function buildChartData($query, ?string $dateFrom, ?string $dateTo, string $range): array
    {
        // Determine grouping
        $groupByWeek  = false;
        $groupByMonth = false;

        if ($range === 'all') {
            $earliest = TokenUsage::whereNull('deleted_at')->min('used_at');
            if ($earliest) {
                $span = Carbon::parse($earliest)->diffInDays(Carbon::today());
                if ($span > 365)      $groupByMonth = true;
                elseif ($span > 90)   $groupByWeek  = true;
            }
        }

        if ($groupByMonth) {
            $rows = (clone $query)
                ->selectRaw("strftime('%Y-%m', used_at) as label, SUM(input_tokens+output_tokens) as tokens, COUNT(*) as requests, COALESCE(SUM(cost),0) as costs")
                ->groupBy('label')->orderBy('label')->get();
            return [
                'labels'   => $rows->pluck('label')->toArray(),
                'tokens'   => $rows->map(fn($r) => (int)$r->tokens)->toArray(),
                'requests' => $rows->map(fn($r) => (int)$r->requests)->toArray(),
                'costs'    => $rows->map(fn($r) => (float)$r->costs)->toArray(),
            ];
        }

        if ($groupByWeek) {
            $rows = (clone $query)
                ->selectRaw("strftime('%Y-W%W', used_at) as label, SUM(input_tokens+output_tokens) as tokens, COUNT(*) as requests, COALESCE(SUM(cost),0) as costs")
                ->groupBy('label')->orderBy('label')->get();
            return [
                'labels'   => $rows->pluck('label')->toArray(),
                'tokens'   => $rows->map(fn($r) => (int)$r->tokens)->toArray(),
                'requests' => $rows->map(fn($r) => (int)$r->requests)->toArray(),
                'costs'    => $rows->map(fn($r) => (float)$r->costs)->toArray(),
            ];
        }

        // Per-day with gap fill
        $rows = (clone $query)
            ->selectRaw('used_at as day, SUM(input_tokens+output_tokens) as tokens, COUNT(*) as requests, COALESCE(SUM(cost),0) as costs')
            ->groupBy('day')->orderBy('day')->get()
            ->keyBy('day');

        if (!$dateFrom) {
            // all-time but short span — use raw labels
            return [
                'labels'   => $rows->keys()->toArray(),
                'tokens'   => $rows->map(fn($r) => (int)$r->tokens)->values()->toArray(),
                'requests' => $rows->map(fn($r) => (int)$r->requests)->values()->toArray(),
                'costs'    => $rows->map(fn($r) => (float)$r->costs)->values()->toArray(),
            ];
        }

        $labels = $tokens = $requests = $costs = [];
        $cur = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        while ($cur <= $end) {
            $d = $cur->format('Y-m-d');
            $labels[]   = $d;
            $tokens[]   = isset($rows[$d]) ? (int)$rows[$d]->tokens   : 0;
            $requests[] = isset($rows[$d]) ? (int)$rows[$d]->requests : 0;
            $costs[]    = isset($rows[$d]) ? (float)$rows[$d]->costs  : 0;
            $cur->addDay();
        }

        return compact('labels', 'tokens', 'requests', 'costs');
    }
}
