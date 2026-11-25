<?php

namespace App\Http\Controllers;

use App\Models\Flat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FlatController extends Controller
{
    public function setup()
    {
        $user = Auth::user();
        $flat = null;
        $members = collect();

        if ($user && $user->flat_id) {
            $flat = Flat::find($user->flat_id);
            $members = User::where('flat_id', $user->flat_id)
                ->orderByDesc('is_admin')
                ->orderBy('name')
                ->get();
        }

        return view('flats.setup', compact('flat', 'members'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $flat = Flat::create([
            'name' => $request->name,
            'invite_code' => Str::upper(Str::random(6)),
        ]);

        $user = Auth::user();
        $user->flat_id = $flat->id;
        $user->is_admin = true;
        $user->save();

        return redirect()->route('expenses.index');
    }

    public function join(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string|exists:flats,invite_code',
        ]);

        $flat = Flat::where('invite_code', Str::upper($request->invite_code))->first();

        $user = Auth::user();
        $user->flat_id = $flat->id;
        $user->is_admin = false;
        $user->save();

        return redirect()->route('expenses.index');
    }

    public function updateName(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->flat_id || !$user->is_admin) {
            abort(403, 'Tylko administrator mieszkania może zmienić nazwę mieszkania.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $flat = Flat::findOrFail($user->flat_id);
        $flat->update([
            'name' => $validated['name'],
        ]);

        return redirect()->back()->with('success', 'Zaktualizowano nazwę mieszkania.');
    }

    public function leave()
    {
        $user = Auth::user();

        if (!$user || !$user->flat_id) {
            return redirect()->route('flats.setup');
        }

        $user->flat_id = null;
        $user->is_admin = false;
        $user->save();

        return redirect()->route('flats.setup')->with('success', 'Opuszczono mieszkanie. Możesz teraz stworzyć nowe lub dołączyć po kodzie.');
    }
}
