@extends('file-manager::layout')

@section('title', 'Trash')

@section('actions')
    <button onclick="if(confirm('Empty Trash?')) document.getElementById('empty-trash-form').submit()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        Empty Trash
    </button>
    <form id="empty-trash-form" action="{{ route('file-manager.trash.empty') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
@endsection

@section('content')
    <div class="glass-panel rounded-2xl overflow-hidden" x-data="{
        async restoreFile(id) {
            if (!confirm('Restore this file?')) return;
            try {
                const response = await fetch(`${window.apiBaseUrl}/trash/${id}/restore`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Restore failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        },
        async deleteFile(id) {
            if (!confirm('Permanently delete this file? This cannot be undone.')) return;
            try {
                const response = await fetch(`${window.apiBaseUrl}/trash/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Delete failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }
    }">
        <table class="min-w-full divide-y divide-gray-200/50">
            <thead class="bg-gray-50/50 backdrop-blur-sm">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                    <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200/50">
                @forelse($files as $file)
                <tr class="hover:bg-white/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100/50 rounded flex items-center justify-center text-gray-400 overflow-hidden border border-gray-200/50">
                                @if($file->type === 'folder')
                                    <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                @elseif(str_starts_with($file->mime_type, 'image/'))
                                    <img src="{{ route('file-manager.preview', $file->id) }}" class="object-cover w-full h-full rounded">
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $file->basename }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->type === 'folder' ? '-' : \Illuminate\Support\Number::fileSize($file->size) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->deleted_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button @click="restoreFile({{ $file->id }})" class="theme-text-primary hover:opacity-80 mr-3 font-semibold">Restore</button>
                        <button @click="deleteFile({{ $file->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">
                        Trash is empty
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
