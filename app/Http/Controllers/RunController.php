<?php

namespace App\Http\Controllers;

use App\Models\Run;
use App\Models\RunLog;
use App\Models\Workflow;
use App\Services\WorkflowExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RunController extends Controller
{
    public function __construct(
        protected WorkflowExecutionService $executionService
    ) {}

    public function store(Request $request, Workflow $workflow): RedirectResponse
    {
        if ($workflow->steps()->count() === 0) {
            return redirect()->route('workflows.show', $workflow)
                ->with('error', 'Cannot run workflow without steps.');
        }

        $run = $this->executionService->execute($workflow);

        return redirect()->route('runs.show', $run)
            ->with('success', 'Workflow execution started.');
    }

    public function show(Run $run): View
    {
        $run->load(['workflow', 'logs.step']);

        return view('runs.show', compact('run'));
    }

    public function index(): View
    {
        $logs = RunLog::with(['run.workflow', 'step'])
            ->orderBy('logged_at', 'desc')
            ->paginate(50);

        return view('runs.logs', compact('logs'));
    }
}
