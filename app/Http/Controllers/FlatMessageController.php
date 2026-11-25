<?php

namespace App\Http\Controllers;

use App\Models\FlatMessage;
use Illuminate\Http\Request;

class FlatMessageController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id) {
            return redirect()->route('flats.setup');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        FlatMessage::create([
            'flat_id' => $user->flat_id,
            'user_id' => $user->id,
            'body' => trim($validated['body']),
        ]);

        return redirect()->back()->with('chat_open', true);
    }
}
