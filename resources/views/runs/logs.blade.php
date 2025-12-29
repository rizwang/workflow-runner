@extends('layouts.app')

@section('title', 'All Run Logs')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">All Run Logs</h1>
        <div class="text-sm text-gray-600">
            Showing {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} logs
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Run</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Step</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logged At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                #{{ $log->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('runs.show', $log->run) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    Run #{{ $log->run_id }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('workflows.show', $log->run->workflow) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                    {{ $log->run->workflow->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($log->step)
                                    <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-1 rounded">
                                        {{ $log->step->type }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded
                                    @if($log->level === 'error') bg-red-100 text-red-800
                                    @elseif($log->level === 'warn') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ strtoupper($log->level) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-md truncate" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->logged_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

