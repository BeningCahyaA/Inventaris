@php
    $record = $log->log_type === 'asset' ? $log->asset : $log->category;
    $archived = $log->log_type === 'asset' && $record?->status === 'sold';
    $editUrl = $record ? ($log->log_type === 'asset'
        ? route($archived ? 'assets.show' : 'assets.edit', $record->id)
        : route('categories.edit', $record->id)) : null;
@endphp
@if($editUrl)
    <a href="{{ $editUrl }}" aria-label="{{ $archived ? 'Lihat arsip' : 'Edit data' }} {{ $record->name }}"
        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
        {{ $archived ? 'Detail arsip' : 'Edit Data' }}
    </a>
@else
    <span class="text-xs text-gray-500">Data sudah dihapus</span>
@endif
