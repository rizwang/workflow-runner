<?php

use App\Http\Controllers\RunController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('workflows.index');
});

Route::resource('workflows', WorkflowController::class);
Route::post('workflows/{workflow}/steps', [WorkflowController::class, 'addStep'])->name('workflows.add-step');
Route::post('workflows/{workflow}/steps/reorder', [WorkflowController::class, 'reorderSteps'])->name('workflows.reorder-steps');
Route::delete('workflows/{workflow}/steps/{step}', [WorkflowController::class, 'deleteStep'])->name('workflows.delete-step');

Route::post('workflows/{workflow}/runs', [RunController::class, 'store'])->name('runs.store');
Route::get('runs/{run}', [RunController::class, 'show'])->name('runs.show');
Route::get('runs-logs', [RunController::class, 'index'])->name('runs.logs');
