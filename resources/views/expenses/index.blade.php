<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>FlatMate - Zarządzanie Wydatkami</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f9; }
        h1, h2 { color: #333; }
        form { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        input, button { padding: 10px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { background: #5cb85c; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #4cae4c; }
        table { width: 100%; background: #fff; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #333; color: white; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>

    <h1>FlatMate - Wydatki w mieszkaniu</h1>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <!-- Formularz dodawania wydatku -->
    <form action="{{ route('expenses.store') }}" method="POST">
        @csrf
        <h2>Dodaj nowy wydatek</h2>
        <input type="text" name="title" placeholder="Tytuł wydatku (np. Zakupy)" required>
        <input type="number" step="0.01" name="amount" placeholder="Kwota (zł)" required>
        <input type="text" name="category" placeholder="Kategoria (np. Jedzenie, Rachunki)">
        <button type="submit">Dodaj wydatek</button>
    </form>

    <!-- Tabela z wydatkami -->
    <h2>Lista wydatków</h2>
    <table>
        <thead>
            <tr>
                <th>Tytuł</th>
                <th>Kwota</th>
                <th>Kategoria</th>
                <th>Data dodania</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->title }}</td>
                    <td>{{ $expense->amount }} zł</td>
                    <td>{{ $expense->category ?? 'Brak' }}</td>
                    <td>{{ $expense->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Brak dodanych wydatków.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
