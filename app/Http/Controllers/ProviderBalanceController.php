<?php

namespace App\Http\Controllers;

use App\Models\ProviderBalance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderBalanceController extends Controller
{
    public function index(): Response
    {
        // Show latest entry per provider, paginated
        $balances = ProviderBalance::orderBy('provider')
            ->orderByDesc('last_updated_at')
            ->paginate(15);

        return Inertia::render('ProviderBalances/Index', [
            'balances' => $balances,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ProviderBalances/Form', [
            'balance' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider'        => ['required', 'string', 'max:100'],
            'balance'         => ['required', 'numeric', 'min:0'],
            'currency'        => ['required', 'string', 'max:10'],
            'last_updated_at' => ['required', 'date'],
            'notes'           => ['nullable', 'string'],
        ]);

        ProviderBalance::create($validated);

        return redirect()->route('provider-balances.index')
            ->with('flash', 'Balance recorded successfully.');
    }

    public function edit(ProviderBalance $providerBalance): Response
    {
        return Inertia::render('ProviderBalances/Form', [
            'balance' => $providerBalance,
        ]);
    }

    public function update(Request $request, ProviderBalance $providerBalance): RedirectResponse
    {
        $validated = $request->validate([
            'provider'        => ['required', 'string', 'max:100'],
            'balance'         => ['required', 'numeric', 'min:0'],
            'currency'        => ['required', 'string', 'max:10'],
            'last_updated_at' => ['required', 'date'],
            'notes'           => ['nullable', 'string'],
        ]);

        $providerBalance->update($validated);

        return redirect()->route('provider-balances.index')
            ->with('flash', 'Balance updated successfully.');
    }

    public function destroy(ProviderBalance $providerBalance): RedirectResponse
    {
        $providerBalance->delete();

        return redirect()->route('provider-balances.index')
            ->with('flash', 'Balance deleted successfully.');
    }
}
