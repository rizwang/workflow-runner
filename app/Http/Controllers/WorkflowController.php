<?php

namespace App\Http\Controllers;

use App\Models\Step;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function index(): View
    {
        $workflows = Workflow::withCount('steps')->latest()->get();
        
        return view('workflows.index', compact('workflows'));
    }

    public function create(): View
    {
        return view('workflows.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workflow = Workflow::create($validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow created successfully.');
    }

    public function show(Workflow $workflow): View
    {
        $workflow->load(['steps' => function ($query) {
            $query->orderBy('order');
        }, 'runs' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('workflows.show', compact('workflow'));
    }

    public function edit(Workflow $workflow): View
    {
        return view('workflows.edit', compact('workflow'));
    }

    public function update(Request $request, Workflow $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workflow->update($validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow updated successfully.');
    }

    public function destroy(Workflow $workflow): RedirectResponse
    {
        $workflow->delete();

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow deleted successfully.');
    }

    public function addStep(Request $request, Workflow $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['delay', 'http_check'])],
            'config' => 'required|json',
        ]);

        $config = json_decode($validated['config'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['config' => 'Invalid JSON configuration.'])->withInput();
        }

        // Validate config based on type
        if ($validated['type'] === 'delay' && !isset($config['seconds'])) {
            return back()->withErrors(['config' => 'Delay step requires "seconds" in config.'])->withInput();
        }

        if ($validated['type'] === 'http_check' && !isset($config['url'])) {
            return back()->withErrors(['config' => 'HTTP check step requires "url" in config.'])->withInput();
        }

        $maxOrder = $workflow->steps()->max('order') ?? -1;

        Step::create([
            'workflow_id' => $workflow->id,
            'type' => $validated['type'],
            'config' => $config,
            'order' => $maxOrder + 1,
        ]);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Step added successfully.');
    }

    public function reorderSteps(Request $request, Workflow $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'step_id' => 'required|exists:steps,id',
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $step = Step::where('workflow_id', $workflow->id)
            ->where('id', $validated['step_id'])
            ->firstOrFail();

        $steps = $workflow->steps()->orderBy('order')->get();
        $currentIndex = $steps->search(function ($s) use ($step) {
            return $s->id === $step->id;
        });

        if ($validated['direction'] === 'up' && $currentIndex > 0) {
            $previousStep = $steps[$currentIndex - 1];
            $tempOrder = $step->order;
            $step->update(['order' => $previousStep->order]);
            $previousStep->update(['order' => $tempOrder]);
        } elseif ($validated['direction'] === 'down' && $currentIndex < $steps->count() - 1) {
            $nextStep = $steps[$currentIndex + 1];
            $tempOrder = $step->order;
            $step->update(['order' => $nextStep->order]);
            $nextStep->update(['order' => $tempOrder]);
        }

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Step reordered successfully.');
    }

    public function deleteStep(Workflow $workflow, Step $step): RedirectResponse
    {
        if ($step->workflow_id !== $workflow->id) {
            abort(404);
        }

        $step->delete();

        // Reorder remaining steps
        $workflow->steps()->orderBy('order')->get()->each(function ($s, $index) {
            $s->update(['order' => $index]);
        });

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Step deleted successfully.');
    }
}
