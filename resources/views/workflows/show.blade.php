@extends('layouts.app')

@section('title', $workflow->name)

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $workflow->name }}</h1>
            @if($workflow->description)
                <p class="text-gray-600 mt-2">{{ $workflow->description }}</p>
            @endif
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('workflows.edit', $workflow) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Edit
            </a>
            <form action="{{ route('runs.store', $workflow) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Run Workflow
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Steps</h2>

            @if($workflow->steps->isEmpty())
                <p class="text-gray-500 mb-4">No steps yet. Add a step to get started.</p>
            @else
                <div class="space-y-3 mb-4">
                    @foreach($workflow->steps as $step)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">{{ $step->type }}</span>
                                        <span class="text-sm text-gray-500">Order: {{ $step->order }}</span>
                                    </div>
                                    <pre class="text-xs bg-gray-50 p-2 rounded overflow-x-auto">{{ json_encode($step->config, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                <div class="flex flex-col space-y-1 ml-4">
                                    @if(!$loop->first)
                                        <form action="{{ route('workflows.reorder-steps', $workflow) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="step_id" value="{{ $step->id }}">
                                            <input type="hidden" name="direction" value="up">
                                            <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm">↑</button>
                                        </form>
                                    @endif
                                    @if(!$loop->last)
                                        <form action="{{ route('workflows.reorder-steps', $workflow) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="step_id" value="{{ $step->id }}">
                                            <input type="hidden" name="direction" value="down">
                                            <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm">↓</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('workflows.delete-step', [$workflow, $step]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this step?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="border-t pt-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Add Step</h3>
                <form action="{{ route('workflows.add-step', $workflow) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" id="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a type</option>
                            <option value="delay">Delay</option>
                            <option value="http_check">HTTP Check</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="config" class="block text-sm font-medium text-gray-700 mb-2">Config (JSON)</label>
                        <textarea name="config" id="config" rows="3" required placeholder='{"seconds": 1} or {"url": "https://example.com"}'
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('config') }}</textarea>
                        @error('config')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Step
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Runs</h2>
            @if($workflow->runs->isEmpty())
                <p class="text-gray-500">No runs yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($workflow->runs as $run)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <a href="{{ route('runs.show', $run) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                        Run #{{ $run->id }}
                                    </a>
                                    <p class="text-sm text-gray-500">{{ $run->started_at->format('M d, Y H:i:s') }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded
                                    @if($run->status === 'succeeded') bg-green-100 text-green-800
                                    @elseif($run->status === 'failed') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($run->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

