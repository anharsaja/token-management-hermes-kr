<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ProviderBalance;
use App\Models\TokenUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $period = in_array($request->get('period'), ['week', 'month', 'year', 'all'])
            ? $request->get('period')
            : 'month';

        [$dateFrom, $dateTo] = $this->periodRange($period);

        // ── Summary cards ────────────────────────────────────────────────
        $totalAgents = Agent::whereNull('deleted_at')->where('status', 'active')->count();

        $activeProviders = ProviderBalance::whereNull('deleted_at')
            ->distinct('provider')
            ->count('provider');

        $usageQuery = TokenUsage::whereNull('token_usages.deleted_at');
        if ($dateFrom) {
            $usageQuery->whereDate('used_at', '>=', $dateFrom)
                       ->whereDate('used_at', '<=', $dateTo);
        }

        $totals = (clone $usageQuery)
            ->selectRaw('COALESCE(SUM(input_tokens + output_tokens), 0) as total_tokens, COALESCE(SUM(cost), 0) as total_cost')
            ->first();

        // ── Top 5 agents ─────────────────────────────────────────────────
        $topAgents = (clone $usageQuery)
            ->selectRaw('agent_id, SUM(input_tokens + output_tokens) as total_tokens, SUM(cost) as total_cost, COUNT(*) as entry_count')
            ->groupBy('agent_id')
            ->orderByDesc('total_tokens')
            ->limit(5)
            ->with('agent')
            ->get()
            ->map(fn ($row) => [
                'agent_id'      => $row->agent_id,
                'agent_name'    => $row->agent?->name ?? '(Unknown)',
                'model_default' => $row->agent?->model_default ?? '—',
                'total_tokens'  => (int) $row->total_tokens,
                'total_cost'    => $row->total_cost !== null ? (float) $row->total_cost : null,
                'entry_count'   => (int) $row->entry_count,
                'is_deleted'    => $row->agent?->deleted_at !== null,
            ]);

        // ── Recent 10 usages (all-time) ───────────────────────────────────
        $recentUsages = TokenUsage::whereNull('deleted_at')
            ->with('agent')
            ->orderByDesc('used_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'used_at'       => $u->used_at?->format('Y-m-d'),
                'agent_name'    => $u->agent?->name ?? '—',
                'model'         => $u->model,
                'input_tokens'  => $u->input_tokens,
                'output_tokens' => $u->output_tokens,
                'cost'          => $u->cost !== null ? (float) $u->cost : null,
            ]);

        // ── Provider balances — latest per provider ───────────────────────
        $providerBalances = ProviderBalance::whereNull('deleted_at')
            ->select('provider', DB::raw('MAX(last_updated_at) as last_updated_at'))
            ->groupBy('provider')
            ->get()
            ->map(function ($row) {
                $latest = ProviderBalance::whereNull('deleted_at')
                    ->where('provider', $row->provider)
                    ->where('last_updated_at', $row->last_updated_at)
                    ->first();
                return [
                    'provider'        => $latest->provider,
                    'balance'         => (float) $latest->balance,
                    'currency'        => $latest->currency,
                    'last_updated_at' => $latest->last_updated_at?->format('Y-m-d'),
                ];
            });

        // ── Chart 1: Token trend ──────────────────────────────────────────
        // Determine if we should group by month (all-time with > 365 days span)
        $groupByMonth = false;
        if ($period === 'all') {
            $earliest = TokenUsage::whereNull('deleted_at')->min('used_at');
            if ($earliest) {
                $daySpan = Carbon::parse($earliest)->diffInDays(Carbon::today());
                $groupByMonth = $daySpan > 365;
            }
        }

        $trendQuery = TokenUsage::whereNull('deleted_at');
        if ($dateFrom) {
            $trendQuery->whereDate('used_at', '>=', $dateFrom)
                       ->whereDate('used_at', '<=', $dateTo);
        }

        if ($groupByMonth) {
            $tokenTrend = $trendQuery
                ->selectRaw("strftime('%Y-%m', used_at) as date, SUM(input_tokens + output_tokens) as total_tokens")
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($r) => ['date' => $r->date, 'total_tokens' => (int) $r->total_tokens])
                ->toArray();
        } else {
            $tokenTrend = $trendQuery
                ->selectRaw('used_at as date, SUM(input_tokens + output_tokens) as total_tokens')
                ->groupBy('used_at')
                ->orderBy('used_at')
                ->get()
                ->map(fn ($r) => ['date' => Carbon::parse($r->date)->format('Y-m-d'), 'total_tokens' => (int) $r->total_tokens])
                ->toArray();
        }

        // ── Chart 2: Token per agent ──────────────────────────────────────
        $tokenPerAgent = (clone $trendQuery)
            ->join('agents', 'token_usages.agent_id', '=', 'agents.id', 'left')
            ->selectRaw('token_usages.agent_id, COALESCE(agents.name, ?) as agent_name, SUM(token_usages.input_tokens + token_usages.output_tokens) as total_tokens, agents.deleted_at as agent_deleted_at', ['(Unknown)'])
            ->groupBy('token_usages.agent_id')
            ->orderByDesc('total_tokens')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'agent_name'   => $r->agent_name,
                'total_tokens' => (int) $r->total_tokens,
                'is_deleted'   => $r->agent_deleted_at !== null,
            ])
            ->toArray();

        // ── Chart 3: Cost per agent ───────────────────────────────────────
        $costPerAgent = (clone $trendQuery)
            ->join('agents', 'token_usages.agent_id', '=', 'agents.id', 'left')
            ->whereNotNull('token_usages.cost')
            ->selectRaw('token_usages.agent_id, COALESCE(agents.name, ?) as agent_name, SUM(token_usages.cost) as total_cost', ['(Unknown)'])
            ->groupBy('token_usages.agent_id')
            ->orderByDesc('total_cost')
            ->get()
            ->map(fn ($r) => [
                'agent_name' => $r->agent_name,
                'total_cost' => (float) $r->total_cost,
            ])
            ->toArray();

        return Inertia::render('Dashboard', [
            'period'            => $period,
            'cards'             => [
                'total_agents'     => $totalAgents,
                'total_tokens'     => (int) $totals->total_tokens,
                'total_cost'       => (float) $totals->total_cost,
                'active_providers' => $activeProviders,
            ],
            'top_agents'        => $topAgents,
            'recent_usages'     => $recentUsages,
            'provider_balances' => $providerBalances,
            'token_trend'       => $tokenTrend,
            'token_per_agent'   => $tokenPerAgent,
            'cost_per_agent'    => $costPerAgent,
            'group_by_month'    => $groupByMonth,
        ]);
    }

    private function periodRange(string $period): array
    {
        $today = Carbon::today();

        return match ($period) {
            'week'  => [$today->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'), $today->format('Y-m-d')],
            'month' => [$today->copy()->startOfMonth()->format('Y-m-d'), $today->format('Y-m-d')],
            'year'  => [$today->copy()->startOfYear()->format('Y-m-d'), $today->format('Y-m-d')],
            default => [null, null],
        };
    }
}
