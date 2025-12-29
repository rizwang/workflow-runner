@extends('layouts.app')

@section('title', 'Run #' . $run->id)

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Run #{{ $run->id }}</h1>
            <p class="text-gray-600 mt-2">
                Workflow: <a href="{{ route('workflows.show', $run->workflow) }}" class="text-blue-600 hover:text-blue-900">{{ $run->workflow->name }}</a>
            </p>
        </div>
        <div>
            <span class="px-3 py-1 text-sm font-semibold rounded
                @if($run->status === 'succeeded') bg-green-100 text-green-800
                @elseif($run->status === 'failed') bg-red-100 text-red-800
                @else bg-yellow-100 text-yellow-800
                @endif">
                {{ ucfirst($run->status) }}
            </span>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                <strong>Started:</strong> {{ $run->started_at->format('M d, Y H:i:s') }}
            </p>
            @if($run->completed_at)
                <p class="text-sm text-gray-600">
                    <strong>Completed:</strong> {{ $run->completed_at->format('M d, Y H:i:s') }}
                </p>
                <p class="text-sm text-gray-600">
                    <strong>Duration:</strong> {{ $run->started_at->diffForHumans($run->completed_at, true) }}
                </p>
            @endif
        </div>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Logs ({{ $run->logs->count() }})</h2>
            <a href="{{ route('runs.logs') }}" class="text-sm text-blue-600 hover:text-blue-900">View All Logs →</a>
        </div>
        @if($run->logs->isEmpty())
            <p class="text-gray-500">No logs available.</p>
        @else
            <div class="space-y-2 mb-4">
                @foreach($run->logs as $log)
                    <div class="border-l-4 pl-4 py-2
                        @if($log->level === 'error') border-red-500 bg-red-50
                        @elseif($log->level === 'warn') border-yellow-500 bg-yellow-50
                        @else border-blue-500 bg-blue-50
                        @endif">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="px-2 py-1 text-xs font-semibold rounded
                                        @if($log->level === 'error') bg-red-200 text-red-800
                                        @elseif($log->level === 'warn') bg-yellow-200 text-yellow-800
                                        @else bg-blue-200 text-blue-800
                                        @endif">
                                        {{ strtoupper($log->level) }}
                                    </span>
                                    @if($log->step)
                                        <span class="text-xs text-gray-600">Step: {{ $log->step->type }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400">Log ID: #{{ $log->id }}</span>
                                </div>
                                <p class="text-sm text-gray-800">{{ $log->message }}</p>
                            </div>
                            <span class="text-xs text-gray-500 ml-4 whitespace-nowrap">
                                {{ $log->logged_at->format('Y-m-d H:i:s') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

