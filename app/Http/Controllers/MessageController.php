<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Method to send a message
    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        // Create a new message
        $message = new Message();
        $message->sender_id = Auth::id(); // Current logged-in user
        $message->recipient_id = $request->recipient_id;
        $message->message = $request->message;
        $message->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully'
        ]);
    }

    // Method to retrieve conversations and messages for the logged-in user
    public function getConversations()
    {
        $currentUser = Auth::user();

        // Get all distinct sender_ids and recipient_ids from messages where the current user is either the sender or recipient
        $senderIds = Message::where('recipient_id', $currentUser->id)->pluck('sender_id')->unique();
        $recipientIds = Message::where('sender_id', $currentUser->id)->pluck('recipient_id')->unique();

        // Merge sender_ids and recipient_ids and get unique user IDs
        $userIds = $senderIds->merge($recipientIds)->unique();

        // Retrieve user details for the conversation participants along with messages
        $conversations = collect();
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            $messages = Message::where(function ($query) use ($userId, $currentUser) {
                $query->where('sender_id', $currentUser->id)
                    ->where('recipient_id', $userId);
            })->orWhere(function ($query) use ($userId, $currentUser) {
                $query->where('sender_id', $userId)
                    ->where('recipient_id', $currentUser->id);
            })->orderBy('created_at')->get();

            // Append conversation details along with messages to the collection
            $conversations->push([
                'user' => $user, // Include user information
                'messages' => $messages
            ]);
        }

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    public function getMessagesBetweenUsers($userId1, $userId2)
    {
        // Fetch all messages where the sender is one user and the recipient is the other, or vice versa
        $messages = Message::where(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)
                ->where('recipient_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)
                ->where('recipient_id', $userId1);
        })->orderBy('created_at')->get();

        if ($messages->isEmpty()) {
            return response()->json([
                'error' => 'No messages found between these users',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }
}
