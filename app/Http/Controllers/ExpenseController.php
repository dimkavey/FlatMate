<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    // Wyświetlanie listy wydatków
 public function index(Request $request)
 {
     $user = $request->user();

     // Jeśli użytkownik w ogóle nie jest zalogowany, kierujemy go do logowania
     if (!$user) {
         return redirect('/login'); // lub odpowiednia ścieżka logowania
     }

     // Jeśli zalogowany użytkownik nie ma przypisanego mieszkania, kierujemy go do ekranu wyboru/tworzenia
     if (!$user->flat_id) {
         return redirect()->route('flats.setup');
     }

     // Pobieramy wydatki powiązane wyłącznie z mieszkaniem użytkownika
     $expenses = Expense::where('flat_id', $user->flat_id)->get();

     return view('expenses.index', compact('expenses'));
 }
    // Zapisywanie nowego wydatku
    public function store(Request $request)
    {
        $user = $request->user();

        // Upewniamy się, że użytkownik ma mieszkanie przed dodaniem wydatku
        if (!$user->flat_id) {
            return redirect()->route('flats.setup');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'category' => 'nullable|string|max:255',
        ]);

        // Przypisujemy wydatek do mieszkania zalogowanego użytkownika
        $validated['flat_id'] = $user->flat_id;

        Expense::create($validated);

        return redirect()->back()->with('success', 'Wydatek został dodany pomyślnie!');
    }
}
