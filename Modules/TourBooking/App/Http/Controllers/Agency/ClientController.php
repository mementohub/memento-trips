<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ClientController
 *
 * Manages agency client records — CRUD operations for the CRM client database.
 *
 * @package Modules\TourBooking\App\Http\Controllers\Agency
 */
final class ClientController extends Controller
{
    public function index(): View
    {
        $clients = AgencyClient::where('agency_user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('tourbooking::agency.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('tourbooking::agency.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'country' => 'nullable|string|max:120',
            'state' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            // GDPR
            'lawful_basis' => 'nullable|string|max:50',
            'consent_email_marketing_at' => 'nullable|date',
            'privacy_notice_version' => 'nullable|string|max:50',
        ]);

        $data['agency_user_id'] = auth()->id();

        AgencyClient::create($data);

        return redirect()
            ->route('agency.tourbooking.clients.index')
            ->with(['message' => 'Client created successfully.', 'alert-type' => 'success']);
    }

    public function edit(AgencyClient $client): View
    {
        if ((int) $client->agency_user_id !== (int) auth()->id()) {
            abort(403);
        }

        return view('tourbooking::agency.clients.edit', compact('client'));
    }

    public function update(Request $request, AgencyClient $client): RedirectResponse
    {
        if ((int) $client->agency_user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'country' => 'nullable|string|max:120',
            'state' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'lawful_basis' => 'nullable|string|max:50',
            'consent_email_marketing_at' => 'nullable|date',
            'privacy_notice_version' => 'nullable|string|max:50',
        ]);

        $client->update($data);

        return back()->with(['message' => 'Client updated successfully.', 'alert-type' => 'success']);
    }

    public function destroy(AgencyClient $client): RedirectResponse
    {
        if ((int) $client->agency_user_id !== (int) auth()->id()) {
            abort(403);
        }

        $client->delete();

        return redirect()
            ->route('agency.tourbooking.clients.index')
            ->with(['message' => 'Client deleted successfully.', 'alert-type' => 'success']);
    }
}