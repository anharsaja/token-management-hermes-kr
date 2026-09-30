<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Agents/Index', [
            'agents' => Agent::latest()->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Agents/Form', [
            'agent' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'provider'      => ['required', 'string', 'max:100'],
            'model_default' => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string'],
            'status'        => ['required', 'in:active,inactive'],
        ]);

        Agent::create($validated);

        return redirect()->route('agents.index')
            ->with('flash', 'Agent created successfully.');
    }

    public function edit(Agent $agent): Response
    {
        return Inertia::render('Agents/Form', [
            'agent' => $agent,
        ]);
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'provider'      => ['required', 'string', 'max:100'],
            'model_default' => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string'],
            'status'        => ['required', 'in:active,inactive'],
        ]);

        $agent->update($validated);

        return redirect()->route('agents.index')
            ->with('flash', 'Agent updated successfully.');
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $agent->delete();

        return redirect()->route('agents.index')
            ->with('flash', 'Agent deleted successfully.');
    }
}
