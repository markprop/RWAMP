<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminChatController extends Controller
{
    /**
     * View all chats (admin read-only access)
     */
    public function index(Request $request)
    {
        $query = Chat::with(['participants', 'latestMessage.sender']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('participants', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by type
        if ($request->filled('type') && in_array($request->type, ['private', 'group'])) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $chatIds = ChatParticipant::where('user_id', $request->user_id)->pluck('chat_id');
            $query->whereIn('id', $chatIds);
        }

        // Filter by deleted messages only
        if ($request->filled('show_deleted_only')) {
            $query->whereHas('messages', function($q) {
                $q->where('is_deleted', true);
            });
        }

        $chats = $query->withCount('messages')
            ->orderBy('last_message_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.chat.index', compact('chats'));
    }

    /**
     * View a specific chat (admin read-only)
     */
    public function show(Chat $chat)
    {
        // Admin can view all chats - no permission check needed
        $messages = $chat->messages()
            ->withTrashed() // Include deleted messages
            ->with(['sender', 'deletedBy'])
            ->orderBy('created_at', 'asc')
            ->get();

        $participants = $chat->participants;

        // Log admin access
        Log::info('Admin viewed chat', [
            'chat_id' => $chat->id,
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
        ]);

        return view('admin.chat.view', [
            'chat' => $chat,
            'messages' => $messages,
            'participants' => $participants,
        ]);
    }

    /**
     * Get chat audit trail
     */
    public function auditTrail(Chat $chat)
    {
        $messages = $chat->messages()
            ->withTrashed()
            ->with(['sender', 'deletedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $auditData = [
            'chat' => $chat,
            'total_messages' => $chat->messages()->count(),
            'deleted_messages' => $chat->messages()->where('is_deleted', true)->count(),
            'messages' => $messages,
        ];

        return response()->json($auditData);
    }
}

