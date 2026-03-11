<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminDeleteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    /**
     * List all admins + pending delete requests.
     */
    public function index()
    {
        $admins = Admin::orderBy('created_at', 'asc')->get();
        $pendingRequests = AdminDeleteRequest::with(['admin', 'requester'])
            ->pending()
            ->get();

        $page_title = 'Manage Admins';

        return view('admin.admins.index', compact('admins', 'pendingRequests', 'page_title'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $page_title = 'Create Admin';
        return view('admin.admins.form', compact('page_title'));
    }

    /**
     * Store a new admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'status'   => 'required|in:enable,disable',
        ]);

        Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => $request->status,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin account created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        $page_title = 'Edit Admin: ' . $admin->name;
        return view('admin.admins.form', compact('admin', 'page_title'));
    }

    /**
     * Update an existing admin.
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $rules = [
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:admins,email,' . $id,
            'status' => 'required|in:enable,disable',
        ];

        // Only validate password if provided
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        $request->validate($rules);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->status = $request->status;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin account updated successfully.');
    }

    /**
     * Request deletion of an admin (requires another admin's approval).
     */
    public function requestDelete($id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = Auth::guard('admin')->user();

        // Cannot delete yourself
        if ($admin->id === $currentAdmin->id) {
            return back()->with('error', 'You cannot request to delete your own account.');
        }

        // Must have at least 2 admins
        if (Admin::count() <= 1) {
            return back()->with('error', 'Cannot delete the only admin account.');
        }

        // Check if there's already a pending request
        $existing = AdminDeleteRequest::where('admin_id', $admin->id)
            ->pending()
            ->first();

        if ($existing) {
            return back()->with('error', 'A delete request for this admin is already pending.');
        }

        AdminDeleteRequest::create([
            'admin_id'     => $admin->id,
            'requested_by' => $currentAdmin->id,
            'status'       => 'pending',
        ]);

        return back()->with('success', 'Delete request submitted. Another admin must approve it.');
    }

    /**
     * Approve a pending delete request → delete the admin.
     */
    public function approveDelete($id)
    {
        $request = AdminDeleteRequest::with('admin')->pending()->findOrFail($id);
        $currentAdmin = Auth::guard('admin')->user();

        // Cannot approve your own request
        if ($request->requested_by === $currentAdmin->id) {
            return back()->with('error', 'You cannot approve your own delete request.');
        }

        // Must keep at least 1 admin
        if (Admin::count() <= 1) {
            return back()->with('error', 'Cannot delete the only admin account.');
        }

        // Mark as approved
        $request->update([
            'status'       => 'approved',
            'responded_by' => $currentAdmin->id,
        ]);

        // Delete the admin
        if ($request->admin) {
            $request->admin->delete();
        }

        return back()->with('success', 'Admin account deleted.');
    }

    /**
     * Reject a pending delete request.
     */
    public function rejectDelete($id)
    {
        $request = AdminDeleteRequest::pending()->findOrFail($id);
        $currentAdmin = Auth::guard('admin')->user();

        $request->update([
            'status'       => 'rejected',
            'responded_by' => $currentAdmin->id,
        ]);

        return back()->with('success', 'Delete request rejected.');
    }
}
