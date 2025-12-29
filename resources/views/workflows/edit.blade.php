@extends('layouts.app')

@section('title', 'Edit Workflow')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Workflow</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('workflows.update', $workflow) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $workflow->name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('description', $workflow->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('workflows.show', $workflow) }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update Workflow
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

