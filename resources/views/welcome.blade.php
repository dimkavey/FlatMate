<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FlatMate - Zarządzaj wydatkami w mieszkaniu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col justify-between font-sans antialiased">

    <!-- Nawigacja górna -->
    <header class="w-full max-w-7xl mx-auto p-6 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="bg-indigo-600 text-white p-2 rounded-xl font-bold text-xl tracking-wider">FM</div>
            <span class="text-2xl font-extrabold tracking-tight">FlatMate</span>
        </div>

        @if (Route::has('login'))
            <nav class="flex items-center gap-4">
                @auth
                    <a href="{{ url('/expenses') }}" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 font-semibold transition">
                        Przejdź do panelu
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-lg text-slate-300 hover:text-white transition font-medium">
                        Zaloguj się
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition shadow-lg shadow-indigo-600/30">
                            Załóż konto
                        </a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>

    <!-- Sekcja Główna (Hero) -->
    <main class="max-w-4xl mx-auto text-center px-6 py-12">
        <span class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-6">
            Aplikacja do zarządzania domem
        </span>
        <h1 class="text-5xl md:text-6xl font-black tracking-tight leading-tight mb-6">
            Dziel rachunki z współlokatorami <br class="hidden sm:block"> <span class="text-indigo-500">bez zbędnych sporów.</span>
        </h1>
        <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-10">
            FlatMate pomaga w łatwym prowadzeniu wspólnego budżetu, przypisywaniu wydatków i zapraszaniu mieszkańców do jednego wspólnego panelu.
        </p>

        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-bold text-lg text-white shadow-xl shadow-indigo-600/25 transition">
                Rozpocznij za darmo
            </a>
            <a href="{{ route('login') }}" class="px-8 py-4 rounded-xl bg-slate-800 hover:bg-slate-700 font-bold text-lg text-slate-200 border border-slate-700 transition">
                Zaloguj się na konto
            </a>
        </div>
    </main>

    <!-- Stopka -->
    <footer class="text-center py-6 text-sm text-slate-500">
        &copy; {{ date('Y') }} FlatMate. Wszystkie prawa zastrzeżone.
    </footer>

</body>
</html>
