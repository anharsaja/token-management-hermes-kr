<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\TokenUsage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TokenUsageController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TokenUsage::with('agent')->latest('used_at')->latest('id');

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('used_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('used_at', '<=', $request->date_to);
        }

        return Inertia::render('TokenUsages/Index', [
            'usages'  => $query->paginate(15)->withQueryString(),
            'agents'  => Agent::withTrashed()->orderBy('name')->get(['id', 'name', 'model_default', 'deleted_at']),
            'filters' => $request->only(['agent_id', 'date_from', 'date_to']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TokenUsages/Form', [
            'usage'  => null,
            'agents' => Agent::withTrashed()->orderBy('name')->get(['id', 'name', 'model_default', 'deleted_at']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'      => ['required', 'integer', 'exists:agents,id'],
            'model'         => ['required', 'string', 'max:100'],
            'input_tokens'  => ['required', 'integer', 'min:0'],
            'output_tokens' => ['required', 'integer', 'min:0'],
            'cost'          => ['nullable', 'numeric', 'min:0'],
            'used_at'       => ['required', 'date'],
            'notes'         => ['nullable', 'string'],
        ]);

        TokenUsage::create($validated);

        return redirect()->route('token-usages.index')
            ->with('flash', 'Token usage recorded successfully.');
    }

    public function edit(TokenUsage $tokenUsage): Response
    {
        return Inertia::render('TokenUsages/Form', [
            'usage'  => $tokenUsage,
            'agents' => Agent::withTrashed()->orderBy('name')->get(['id', 'name', 'model_default', 'deleted_at']),
        ]);
    }

    public function update(Request $request, TokenUsage $tokenUsage): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'      => ['required', 'integer', 'exists:agents,id'],
            'model'         => ['required', 'string', 'max:100'],
            'input_tokens'  => ['required', 'integer', 'min:0'],
            'output_tokens' => ['required', 'integer', 'min:0'],
            'cost'          => ['nullable', 'numeric', 'min:0'],
            'used_at'       => ['required', 'date'],
            'notes'         => ['nullable', 'string'],
        ]);

        $tokenUsage->update($validated);

        return redirect()->route('token-usages.index')
            ->with('flash', 'Token usage updated successfully.');
    }

    public function destroy(TokenUsage $tokenUsage): RedirectResponse
    {
        $tokenUsage->delete();

        return redirect()->route('token-usages.index')
            ->with('flash', 'Token usage deleted successfully.');
    }
}
