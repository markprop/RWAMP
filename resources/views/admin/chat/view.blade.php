@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">{{ $chat->display_name }}</h1>
                <p class="text-gray-500">
                    {{ $chat->type === 'group' ? 'Group Chat' : 'Private Chat' }}
                    • {{ $participants->count() }} participants
                </p>
            </div>
            <a href="{{ route('admin.chats.index') }}" class="text-primary hover:underline">
                ← Back to Chats
            </a>
        </div>

        <!-- Participants Info -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-semibold mb-2">Participants:</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($participants as $participant)
                    <div class="px-3 py-1 bg-white rounded border">
                        <p class="font-semibold">{{ $participant->name }}</p>
                        <p class="text-xs text-gray-500">{{ $participant->email }}</p>
                        <p class="text-xs text-gray-500">{{ $participant->phone }}</p>
                        <span class="text-xs bg-primary text-white px-2 py-0.5 rounded">{{ $participant->role }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Messages -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="font-semibold mb-4">Messages (Read-Only)</h3>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($messages as $message)
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-semibold">{{ $message->sender->name }}</p>
                                <p class="text-xs text-gray-500">{{ $message->sender->email }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $message->created_at->format('M d, Y H:i') }}</p>
                                @if($message->is_deleted)
                                    <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                                        Deleted by {{ $message->deletedBy->name ?? 'User' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($message->is_deleted)
                            <p class="text-gray-400 italic">This message was deleted</p>
                        @else
                            @if($message->media_type === 'receipt')
                                <div class="mb-2">
                                    <p class="text-sm font-semibold">📄 Payment Receipt</p>
                                    @if($message->media_path)
                                        <img src="{{ asset('storage/' . $message->media_path) }}" alt="Receipt" class="mt-2 max-w-xs rounded">
                                    @endif
                                </div>
                            @elseif($message->media_type === 'location')
                                <div class="mb-2">
                                    <p class="text-sm font-semibold">📍 Location Shared</p>
                                    @if($message->location_data)
                                        <a 
                                            href="https://maps.google.com/?q={{ $message->location_data['lat'] }},{{ $message->location_data['lng'] }}"
                                            target="_blank"
                                            class="text-sm text-primary underline"
                                        >
                                            View on Map
                                        </a>
                                    @endif
                                </div>
                            @elseif($message->media_type === 'image')
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $message->media_path) }}" alt="Image" class="max-w-xs rounded">
                                </div>
                            @elseif($message->content)
                                <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                            @endif
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-8">No messages in this chat</p>
                @endforelse
            </div>
        </div>

        <!-- Admin Note -->
        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800">
                <strong>Admin Note:</strong> This is a read-only view. You cannot send messages in user chats. 
                Deleted messages are still visible for audit purposes.
            </p>
        </div>
    </div>
</div>
@endsection

