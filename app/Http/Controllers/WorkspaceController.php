<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $workspaces = $request->user()->workspaces()->get();
        
        return view('workspaces.index', compact('workspaces'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $workspace = $request->user()->workspaces()->create($validated);

            return redirect()->route('workspaces.page')
                ->with('success', 'Workspace created successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function show(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        return view('workspace.index', compact('workspace'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $workspace->update($validated);

            return redirect()->route('workspaces.page')
                ->with('success', 'Workspace updated successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function destroy(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        $workspace->delete();

        return redirect()->route('workspaces.page')
            ->with('success', 'Workspace deleted successfully');
    }
}
