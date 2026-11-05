<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flat;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FlatController extends Controller
{
    // Wyświetlanie formularza tworzenia / dołączania do mieszkania
    public function setup()
    {
        return view('flats.setup'); // Upewnij się, że masz taki widok w resources/views/flat/setup.blade.php
    }

    // Tworzenie nowego mieszkania
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

    // Dołączanie do istniejącego mieszkania za pomocą kodu
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
}
