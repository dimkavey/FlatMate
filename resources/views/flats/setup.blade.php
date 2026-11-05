<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlatMate - Wybór Mieszkania</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { display: flex; gap: 20px; max-width: 800px; width: 100%; padding: 20px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); flex: 1; }
        h2 { margin-top: 0; color: #333; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        button.blue { background-color: #007bff; }
        .error { color: red; font-size: 0.9em; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <!-- Formularz tworzenia mieszkania -->
    <div class="card">
        <h2>Stwórz nowe mieszkanie</h2>
        <p>Będziesz administratorem i ustalisz opłaty oraz zaprosisz współlokatorów.</p>
        <form action="{{ route('flat.create') }}" method="POST">
            @csrf
            <label for="name">Nazwa mieszkania:</label>
            <input type="text" id="name" name="name" placeholder="np. Słoneczna 12/4" required>
            <button type="submit">Stwórz Mieszkanie</button>
        </form>
    </div>

    <!-- Formularz dołączania do mieszkania -->
    <div class="card">
        <h2>Dołącz do mieszkania</h2>
        <p>Wpisz kod zaproszenia podany przez głównego lokatora.</p>
        @if($errors->has('invite_code'))
            <div class="error">{{ $errors->first('invite_code') }}</div>
        @endif
        <form action="{{ route('flat.join') }}" method="POST">
            @csrf
            <label for="invite_code">Kod zaproszenia:</label>
            <input type="text" id="invite_code" name="invite_code" placeholder="np. X8Y9Z1A2" required>
            <button type="submit" class="blue">Dołącz do Mieszkania</button>
        </form>
    </div>
</div>

</body>
</html>
