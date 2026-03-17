<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkareaController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FolderController;
use App\Models\Workspace;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // File management routes
    Route::get('/files', [FileUploadController::class, 'listFiles'])->name('files.list');
    Route::post('/files/upload', [FileUploadController::class, 'upload'])->name('files.upload');
    Route::delete('/files/delete', [FileUploadController::class, 'deleteFile'])->name('files.delete');
    
    // Folder management routes
    Route::post('/folders/create', [FileUploadController::class, 'createFolder'])->name('folders.create');
    Route::delete('/folders/delete', [FileUploadController::class, 'deleteFolder'])->name('folders.delete');
    
    // File manager page
    Route::get('/file-manager', function () {
        return view('file-manager');
    })->name('file-manager');
    
    // Workspace management routes
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::get('/workspaces-page', [WorkspaceController::class, 'index'])->name('workspaces.page');
    Route::get('/workspaces/create', function () {
        return view('workspaces.create');
    })->name('workspaces.create');
    Route::get('/workspaces/{workspace}/edit', function (Workspace $workspace) {
        return view('workspaces.edit', ['workspace' => $workspace]);
    })->name('workspaces.edit');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspaces.show');
    Route::put('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');
    Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy'])->name('workspaces.destroy');
    
    // Project management routes
    Route::get('/workspaces/{workspace}/workspace', [WorkareaController::class, 'index'])->name('workspace.index');
    Route::get('/workspaces/{workspace}/workspace-page', function (Workspace $workspace) {
        return view('workspace.index', ['workspace' => $workspace]);
    })->name('workspace.page');
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

    //Opening for project
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    
    // Folder management routes
    Route::post('/folders', [FolderController::class,'store'])->name('folders.store');
    Route::get('/folders/{folder}', [FolderController::class,'show'])->name('folders.show');
    Route::delete('/folders/{folder}', [FolderController::class,'destroy'])->name('folders.destroy');
});


require __DIR__.'/auth.php';
