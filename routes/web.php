<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkareaController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\AssetPreviewController;
use App\Models\Workspace;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Folder management routes (pure Laravel)
    Route::get('/folder-manager', [FolderController::class, 'index'])->name('folder-manager');
    Route::post('/folders/store', [FolderController::class, 'store'])->name('folders.store');
    Route::post('/folders/upload', [FolderController::class, 'uploadFiles'])->name('folders.upload');
    Route::delete('/folders/file', [FolderController::class, 'deleteFile'])->name('folders.file.delete');
    Route::get('/folders/{folder}', [FolderController::class, 'show'])->name('folders.show');
    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');
    Route::post('/assets/move', [FolderController::class, 'moveAsset'])->name('assets.move');
    Route::get('/projects/{project}/folder-tree', [FolderController::class, 'getFolderTree'])->name('projects.folder-tree');
    
    // Workspace management routes
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::get('/workspaces-page', [WorkspaceController::class, 'index'])->name('workspaces.page');
    Route::get('/workspaces/share-index', [WorkspaceController::class, 'shareIndex'])->name('workspaces.share-index');
    Route::get('/workspaces/create', function () {
        return view('workspaces.create');
    })->name('workspaces.create');
    Route::get('/workspaces/{workspace}/edit', function (Workspace $workspace) {
        return view('workspaces.edit', ['workspace' => $workspace]);
    })->name('workspaces.edit');
    Route::get('/workspaces/{workspace}/share', [WorkspaceController::class, 'share'])->name('workspaces.share');
    Route::post('/workspaces/{workspace}/invite', [WorkspaceController::class, 'invite'])->name('workspaces.invite');
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceController::class, 'removeMember'])->name('workspaces.remove-member');
    Route::patch('/workspaces/{workspace}/members/{user}', [WorkspaceController::class, 'updateMemberRole'])->name('workspaces.update-member');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspaces.show');
    Route::put('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');
    Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy'])->name('workspaces.destroy');
    
    // Project management routes
    Route::get('/workspaces/{workspace}/workspace', [WorkareaController::class, 'index'])->name('workspace.index');
    Route::get('/workspaces/{workspace}/workspace-page', [WorkspaceController::class, 'show'])->name('workspace.page');
    Route::get('/workspaces/{workspace}/create', function (Workspace $workspace) {
        return view('workspace.create', ['workspace' => $workspace]);
    })->name('workspace.create');
    Route::get('/workspaces/{workspace}/projects/{project}/edit', function (Workspace $workspace, Project $project) {
        return view('workspace.edit', ['workspace' => $workspace, 'project' => $project]);
    })->name('workspace.edit');
    Route::post('/workspaces/{workspace}/projects', [WorkareaController::class, 'store'])->name('workspace.store');
    Route::get('/workspaces/{workspace}/projects/{project}', [WorkareaController::class, 'show'])->name('workspace.show');
    Route::put('/workspaces/{workspace}/projects/{project}', [WorkareaController::class, 'update'])->name('workspace.update');
    Route::delete('/workspaces/{workspace}/projects/{project}', [WorkareaController::class, 'destroy'])->name('workspace.destroy');

    // Activity Log route
    Route::get('/activity-log', [WorkspaceController::class, 'activityLog'])->name('activity.log');

    // Debug route to check workspace users status
    Route::get('/debug/workspace-users/{workspace}', function (Workspace $workspace) {
        $workspaceUsers = \App\Models\WorkspaceUser::where('workspace_id', $workspace->id)
            ->with('user')
            ->get()
            ->map(function ($wu) {
                return [
                    'id' => $wu->id,
                    'user_name' => $wu->user->name,
                    'user_email' => $wu->user->email,
                    'role' => $wu->role,
                    'status' => $wu->status,
                    'created_at' => $wu->created_at,
                    'updated_at' => $wu->updated_at,
                ];
            });
        
        return response()->json($workspaceUsers);
    })->name('debug.workspace-users');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::patch('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/{id}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/approve', [NotificationController::class, 'approveInvitation'])->name('notifications.approve');
    Route::post('/notifications/{id}/reject', [NotificationController::class, 'rejectInvitation'])->name('notifications.reject');
    Route::post('/notifications/{id}/approve-workspace', [NotificationController::class, 'approveWorkspaceRequest'])->name('notifications.approve-workspace');
    Route::post('/notifications/{id}/reject-workspace', [NotificationController::class, 'rejectWorkspaceRequest'])->name('notifications.reject-workspace');
    Route::post('/notifications/{id}/approve-project', [NotificationController::class, 'approveProjectRequest'])->name('notifications.approve-project');
    Route::post('/notifications/{id}/reject-project', [NotificationController::class, 'rejectProjectRequest'])->name('notifications.reject-project');

    // Theme toggle route
    Route::post('/theme/toggle', [ThemeController::class, 'toggle'])->name('theme.toggle');

    //Opening for project
    Route::get('/projects/share-index', [ProjectController::class, 'shareIndex'])->name('projects.share-index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/share', [ProjectController::class, 'share'])->name('projects.share');
    Route::post('/projects/{project}/invite', [ProjectController::class, 'invite'])->name('projects.invite');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.remove-member');
    Route::delete('/project-collaborators/{collaborator}', [ProjectController::class, 'removeCollaborator'])->name('projects.remove-collaborator');
    Route::post('/projects/{project}/accept-invitation', [ProjectController::class, 'acceptInvitation'])->name('projects.accept-invitation');
    Route::post('/projects/decline-invitation/{notificationId}', [ProjectController::class, 'declineInvitation'])->name('projects.decline-invitation');

    // Asset preview routes
    Route::get('/assets/{asset}/preview', [AssetPreviewController::class, 'preview'])->name('assets.preview');
    Route::get('/assets/{asset}/thumbnail/{size?}', [AssetPreviewController::class, 'thumbnail'])->name('assets.thumbnail');
    Route::get('/assets/{asset}/metadata', [AssetPreviewController::class, 'metadata'])->name('assets.metadata');
});


require __DIR__.'/auth.php';
