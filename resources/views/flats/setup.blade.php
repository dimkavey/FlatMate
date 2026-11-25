<!DOCTYPE html>
<html lang="pl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustawienia mieszkania — FlatMate</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#090d16] text-slate-100 antialiased selection:bg-indigo-500/30 selection:text-indigo-200">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-32 left-1/2 h-80 w-[36rem] -translate-x-1/2 rounded-full bg-indigo-600/15 blur-3xl"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-5xl flex-col justify-between px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold tracking-tight text-white shadow-lg shadow-indigo-600/30 transition group-hover:bg-indigo-500">
                        FM
                    </span>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white">FlatMate</span>
                        @if(isset($flat))
                            <span class="ml-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-0.5 text-xs font-medium text-indigo-300">
                                {{ $flat->name }}
                            </span>
                        @endif
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-slate-400 sm:inline">
                        {{ Auth::user()->name ?? '' }}
                    </span>
                    @if(isset($flat))
                        <a href="{{ route('expenses.index') }}" class="rounded-lg border border-slate-800 bg-slate-900/80 px-3.5 py-2 text-xs font-medium text-slate-300 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white">
                            Wróć do wydatków
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-800 bg-slate-900/80 px-3.5 py-2 text-xs font-medium text-slate-300 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white">
                            Wyloguj się
                        </button>
                    </form>
                </div>
            </header>

            @if (session('success'))
                <div class="mt-6 rounded-xl border border-emerald-500/30 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-rose-500/30 bg-rose-950/40 p-4 text-sm text-rose-200">
                    <p class="font-semibold text-rose-300">Popraw błędy w formularzu:</p>
                    <ul class="mt-1.5 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($flat))
                <div class="mt-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-white">
                            Ustawienia <span class="text-indigo-400">mieszkania</span>
                        </h1>
                        <p class="mt-1.5 text-sm text-slate-400">
                            Informacje o gospodarstwie domowym, kod zaproszenia dla współlokatorów oraz lista uczestników.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-800 bg-[#111827]/90 px-4 py-3 text-sm">
                        <span class="text-slate-400">Kod zaproszenia:</span>
                        <span class="ml-2 font-mono font-semibold tracking-wider text-indigo-300">{{ $flat->invite_code }}</span>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <section class="rounded-2xl border border-slate-800/90 bg-[#111827]/90 p-6 shadow-xl shadow-black/30">
                        <h2 class="text-lg font-bold text-white">Dane mieszkania</h2>
                        <p class="mt-1 text-xs text-slate-400">
                            Przekaż kod <strong class="font-mono text-indigo-300">{{ $flat->invite_code }}</strong> współlokatorom, aby mogli dołączyć podczas konfiguracji konta.
                        </p>

                        @if(Auth::user()?->is_admin)
                            <form action="{{ route('flat.update-name') }}" method="POST" class="mt-5 space-y-4">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="flat_name" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Nazwa mieszkania
                                    </label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="flat_name"
                                        value="{{ old('name', $flat->name) }}"
                                        required
                                        class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                </div>
                                <button
                                    type="submit"
                                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500"
                                >
                                    Zapisz nazwę
                                </button>
                            </form>
                        @else
                            <div class="mt-5 rounded-xl border border-slate-800 bg-slate-950/60 p-4 text-sm">
                                <div class="text-slate-400">Nazwa mieszkania:</div>
                                <div class="mt-1 font-semibold text-white">{{ $flat->name }}</div>
                            </div>
                        @endif

                        <div class="mt-6 border-t border-slate-800/80 pt-5">
                            <form action="{{ route('flat.leave') }}" method="POST" onsubmit="return confirm('Na pewno chcesz opuścić to mieszkanie?');">
                                @csrf
                                <button
                                    type="submit"
                                    class="rounded-xl border border-rose-500/30 bg-rose-950/30 px-4 py-2 text-xs font-semibold text-rose-300 transition hover:bg-rose-900/40"
                                >
                                    Opuść mieszkanie
                                </button>
                            </form>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-800/90 bg-[#111827]/90 p-6 shadow-xl shadow-black/30">
                        <h2 class="text-lg font-bold text-white">
                            Uczestnicy mieszkania ({{ isset($members) ? $members->count() : 0 }})
                        </h2>
                        <p class="mt-1 text-xs text-slate-400">
                            Koszty wspólne oraz rachunki dzielą się automatycznie na aktualną liczbę osób.
                        </p>

                        <ul class="mt-4 divide-y divide-slate-800/80">
                            @foreach ($members ?? [] as $member)
                                <li class="flex items-center justify-between py-3 text-sm">
                                    <div>
                                        <span class="font-medium text-white">{{ $member->name }}</span>
                                        @if((int) $member->id === (int) Auth::id())
                                            <span class="ml-1.5 text-xs text-indigo-400">(Ty)</span>
                                        @endif
                                    </div>
                                    @if($member->is_admin)
                                        <span class="rounded-full border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-0.5 text-xs font-medium text-indigo-300">
                                            Administrator
                                        </span>
                                    @else
                                        <span class="rounded-full border border-slate-700 bg-slate-800/80 px-2.5 py-0.5 text-xs font-medium text-slate-300">
                                            Mieszkaniec
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>
            @else
                <div class="mt-8 text-center">
                    <span class="inline-block rounded-full border border-indigo-500/30 bg-indigo-500/10 px-3.5 py-1 text-xs font-medium text-indigo-300">
                        Krok startowy • Wybór gospodarstwa domowego
                    </span>
                    <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white">
                        Skonfiguruj swoje <span class="text-indigo-400">mieszkanie</span>
                    </h1>
                    <p class="mt-2 text-sm text-slate-400">
                        Załóż nowe mieszkanie jako administrator albo dołącz do istniejącej grupy współlokatorów za pomocą kodu zaproszenia.
                    </p>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    <section class="rounded-2xl border border-slate-800/90 bg-[#111827]/90 p-6 shadow-xl shadow-black/30">
                        <h2 class="text-lg font-bold text-white">Stwórz nowe mieszkanie</h2>
                        <p class="mt-1 text-xs text-slate-400">
                            Zostaniesz administratorem mieszkania, otrzymasz unikalny kod zaproszenia i będziesz mógł dodawać wspólne wydatki.
                        </p>
                        <form action="{{ route('flat.create') }}" method="POST" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                    Nazwa mieszkania
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    placeholder="np. Słoneczna 12/4"
                                    required
                                    class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                >
                            </div>
                            <button
                                type="submit"
                                class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500"
                            >
                                Stwórz mieszkanie
                            </button>
                        </form>
                    </section>

                    <section class="rounded-2xl border border-slate-800/90 bg-[#111827]/90 p-6 shadow-xl shadow-black/30">
                        <h2 class="text-lg font-bold text-white">Dołącz do mieszkania</h2>
                        <p class="mt-1 text-xs text-slate-400">
                            Masz już kod zaproszenia od współlokatora lub administratora? Wpisz go poniżej, aby połączyć się ze wspólnym panelem.
                        </p>
                        <form action="{{ route('flat.join') }}" method="POST" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label for="invite_code" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                    Kod zaproszenia
                                </label>
                                <input
                                    type="text"
                                    name="invite_code"
                                    id="invite_code"
                                    placeholder="NP. X8Y9Z1A2"
                                    required
                                    class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm uppercase text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                >
                            </div>
                            <button
                                type="submit"
                                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                            >
                                Dołącz do mieszkania
                            </button>
                        </form>
                    </section>
                </div>
            @endif
        </div>

        <footer class="mt-10 pt-6 text-center text-xs text-slate-500">
            © {{ date('Y') }} FlatMate. Wszystkie prawa zastrzeżone.
        </footer>
    </div>
</body>
</html>
