<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    // Wyświetlanie listy wydatków
    public function index()
    {
        $expenses = Expense::all();
        return view('expenses.index', compact('expenses'));
    }

    // Zapisywanie nowego wydatku
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'category' => 'nullable|string|max:255',
        ]);

        Expense::create($request->all());

        return redirect()->back()->with('success', 'Wydatek został dodany pomyślnie!');
    }
}
