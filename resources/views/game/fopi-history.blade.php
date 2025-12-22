@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">FOPI Game History</h1>
            <p class="mt-1 text-sm text-gray-600">
                All recorded FOPI game events across your sessions. Times are shown in your account timezone.
            </p>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm whitespace-nowrap">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">When</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Session</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Event</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $event->created_at->format('M d, Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="font-mono text-xs">
                                        #{{ $event->session_id }}
                                    </span>
                                    <div class="text-xs text-gray-500">
                                        {{ optional($event->session)->status ?? 'n/a' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        bg-gray-100 text-gray-800">
                                        {{ $event->event_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700 max-w-md">
                                    <pre class="whitespace-pre-wrap break-words text-[11px] bg-gray-50 rounded-md p-2 border border-gray-100">
{{ json_encode($event->details, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}
                                    </pre>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    No FOPI game events recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

