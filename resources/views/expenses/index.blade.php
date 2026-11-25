<!DOCTYPE html>
<html lang="pl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wydatki w mieszkaniu — FlatMate</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#090d16] text-slate-100 antialiased selection:bg-indigo-500/30 selection:text-indigo-200">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-32 left-1/2 h-80 w-[36rem] -translate-x-1/2 rounded-full bg-indigo-600/15 blur-3xl"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col justify-between px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <!-- Górny pasek nawigacji -->
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
                    <a href="{{ url('/flat/setup') }}" class="rounded-lg border border-slate-800 bg-slate-900/80 px-3.5 py-2 text-xs font-medium text-slate-300 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white">
                        Ustawienia mieszkania
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-800 bg-slate-900/80 px-3.5 py-2 text-xs font-medium text-slate-300 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white">
                            Wyloguj się
                        </button>
                    </form>
                </div>
            </header>

            @php
                $membersCount = isset($members) ? max($members->count(), 1) : 1;
                $totalAmount = isset($expenses) ? $expenses->sum('amount') : 0;
                $expensesCount = isset($expenses) ? $expenses->count() : 0;
                $billsTotalAmount = isset($bills) ? $bills->sum('amount') : 0;
                $myDebtsCollection = isset($myDebts) ? collect($myDebts) : collect();
                $toPayValue = $myTotalToPay ?? 0;
                $pendingApprovalValue = $myTotalPendingApproval ?? 0;
                $othersOweValue = $othersOweMeTotal ?? 0;
                $othersPendingValue = $othersPendingApprovalToMeTotal ?? 0;
                $isCurrentUserAdmin = isset($admin) && $admin && (int) $admin->id === (int) Auth::id();
                $chatMessages = isset($messages) ? collect($messages) : collect();
                $isChatOpenDefault = (bool) session('chat_open', false) || $errors->has('body');
            @endphp

            <!-- Nagłówek -->
            <div class="mt-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">
                        Twoje rozliczenia w <span class="text-indigo-400">mieszkaniu</span>
                    </h1>
                    <p class="mt-1.5 text-sm text-slate-400">
                        Najważniejsze informacje o tym, ile masz do spłaty, komu przekazać pieniądze i do kiedy — resztę sekcji rozwiniesz poniżej.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl border border-slate-800 bg-[#111827]/90 px-4 py-3 text-sm">
                        <span class="text-slate-400">Uczestników:</span>
                        <span class="ml-1.5 font-semibold text-white">{{ isset($members) ? $members->count() : 1 }}</span>
                    </div>

                    @if(isset($flat) && !empty($flat->invite_code))
                        <div class="rounded-xl border border-slate-800 bg-[#111827]/90 px-4 py-3 text-sm">
                            <span class="text-slate-400">Kod zaproszenia:</span>
                            <span class="ml-2 font-mono font-semibold tracking-wider text-indigo-300">{{ $flat->invite_code }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Komunikaty sukcesu i błędów -->
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

            <!-- Główny panel dla zalogowanego użytkownika: ile spłacić, komu i do kiedy -->
            <section class="mt-6 rounded-2xl border border-indigo-500/25 bg-[#111827]/95 p-6 shadow-xl shadow-black/30">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-amber-500/25 bg-amber-950/20 p-4">
                        <p class="text-xs font-medium uppercase tracking-wider text-amber-300/90">Do spłaty przez Ciebie</p>
                        <p class="mt-1.5 text-2xl font-extrabold text-amber-300">{{ number_format($toPayValue, 2, ',', ' ') }} zł</p>
                        <p class="mt-1 text-xs text-slate-400">Kwota jeszcze niezgłoszona do akceptacji</p>
                    </div>

                    <div class="rounded-xl border border-sky-500/25 bg-sky-950/20 p-4">
                        <p class="text-xs font-medium uppercase tracking-wider text-sky-300/90">Oczekuje na sprawdzenie</p>
                        <p class="mt-1.5 text-2xl font-extrabold text-sky-300">{{ number_format($pendingApprovalValue, 2, ',', ' ') }} zł</p>
                        <p class="mt-1 text-xs text-slate-400">Zgłoszone przez Ciebie wpłaty</p>
                    </div>

                    <div class="rounded-xl border border-emerald-500/25 bg-emerald-950/20 p-4">
                        <p class="text-xs font-medium uppercase tracking-wider text-emerald-300/90">Inni mają oddać Tobie</p>
                        <p class="mt-1.5 text-2xl font-extrabold text-emerald-300">{{ number_format($othersOweValue, 2, ',', ' ') }} zł</p>
                        <p class="mt-1 text-xs text-slate-400">
                            @if($othersPendingValue > 0)
                                Do zatwierdzenia przez Ciebie: {{ number_format($othersPendingValue, 2, ',', ' ') }} zł
                            @else
                                Za Twoje wydatki oraz rachunki (jeśli jesteś adminem)
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-800/80 pt-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-base font-bold text-white">Szczegóły: ile, komu i do kiedy przekazać</h2>
                        <span class="text-xs text-slate-400">
                            Rachunki trafiają do: <strong class="text-slate-200">{{ $admin->name ?? 'Administrator mieszkania' }}</strong>
                        </span>
                    </div>

                    <div class="mt-4 space-y-2.5">
                        @forelse ($myDebtsCollection as $debt)
                            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-800/90 bg-slate-950/70 px-4 py-3 text-sm">
                                <div class="space-y-0.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-white">{{ $debt['title'] }}</span>
                                        @if($debt['status'] === \App\Models\FlatBillPayment::STATUS_PENDING_APPROVAL)
                                            <span class="rounded-md border border-sky-500/30 bg-sky-950/50 px-2 py-0.5 text-[11px] font-medium text-sky-300">
                                                Do sprawdzenia przez admina
                                            </span>
                                        @else
                                            <span class="rounded-md border border-amber-500/30 bg-amber-950/40 px-2 py-0.5 text-[11px] font-medium text-amber-300">
                                                Do zapłaty
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        Komu przekazać: <strong class="text-indigo-300">{{ $debt['recipient'] }}</strong>
                                        <span class="mx-1.5 text-slate-600">•</span>
                                        Termin: <strong class="text-slate-200">{{ $debt['due'] }}</strong>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <div class="text-base font-extrabold text-white">
                                            {{ number_format($debt['amount'], 2, ',', ' ') }} zł
                                        </div>
                                    </div>

                                    @if($debt['status'] === \App\Models\FlatBillPayment::STATUS_PENDING)
                                        <form action="{{ $debt['action_route'] }}" method="POST">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-600/20 transition hover:bg-indigo-500"
                                            >
                                                Zgłoś opłacenie
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-emerald-500/25 bg-emerald-950/20 px-4 py-5 text-center text-sm text-emerald-200">
                                Nie masz obecnie żadnych nieopłaconych udziałów w rachunkach ani wydatkach. Wszystko rozliczone!
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- Dwa rozwijane przyciski na dole: Stałe opłaty i rachunki oraz Lista wydatków -->
            <div class="mt-8 space-y-5">
                <!-- Rozwijana sekcja 1: Stałe opłaty i rachunki -->
                <details class="group rounded-2xl border border-slate-800/90 bg-[#111827]/90 shadow-xl shadow-black/30">
                    <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-4 p-6 transition hover:bg-slate-900/40 rounded-2xl">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-bold text-white">Stałe opłaty i rachunki</h2>
                                <span class="rounded-full border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-0.5 text-xs font-semibold text-indigo-300">
                                    {{ number_format($billsTotalAmount, 2, ',', ' ') }} zł
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                Czynsz, prąd i gaz — kliknij, aby rozwinąć rachunki, formularz dodawania oraz rozliczenie mieszkańców.
                            </p>
                        </div>

                        <span class="rounded-xl border border-slate-700 bg-slate-800/90 px-4 py-2 text-xs font-semibold text-slate-200 transition group-open:border-indigo-500/50 group-open:bg-indigo-600 group-open:text-white">
                            Rozwiń / Zwiń
                        </span>
                    </summary>

                    <div class="border-t border-slate-800/80 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs text-slate-400">
                                Kwota dzieli się automatycznie na {{ isset($members) ? $members->count() : 1 }} {{ (isset($members) && $members->count() === 1) ? 'osobę' : 'osoby/osób' }} w mieszkaniu. Administrator opłaca rachunek jako pierwszy, a reszta mieszkańców zwraca swój udział. Termin płatności: 10. dzień następnego miesiąca.
                            </p>
                            @if(Auth::user()?->is_admin)
                                <span class="rounded-full border border-indigo-500/30 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-300">
                                    Tryb administratora
                                </span>
                            @else
                                <span class="rounded-full border border-slate-700 bg-slate-800/80 px-3 py-1 text-xs font-medium text-slate-400">
                                    Podgląd mieszkańca
                                </span>
                            @endif
                        </div>

                        @if(Auth::user()?->is_admin)
                            <form action="{{ route('flat-bills.store') }}" method="POST" class="mt-5 grid gap-4 border-b border-slate-800/80 pb-6 md:grid-cols-4 md:items-end">
                                @csrf

                                <div>
                                    <label for="type" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Rodzaj opłaty
                                    </label>
                                    <select
                                        name="type"
                                        id="type"
                                        required
                                        class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                        <option value="rent" class="bg-slate-900 text-white">Czynsz (stała kwota)</option>
                                        <option value="electricity" class="bg-slate-900 text-white">Prąd (rachunek miesięczny)</option>
                                        <option value="gas" class="bg-slate-900 text-white">Gaz (rachunek miesięczny)</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="billing_month" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Miesiąc rozliczeniowy
                                    </label>
                                    <input
                                        type="month"
                                        name="billing_month"
                                        id="billing_month"
                                        value="{{ old('billing_month', now()->format('Y-m')) }}"
                                        required
                                        class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                </div>

                                <div>
                                    <label for="bill_amount" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Kwota (zł)
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        name="amount"
                                        id="bill_amount"
                                        value="{{ old('amount') }}"
                                        placeholder="np. 1200.00"
                                        required
                                        class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                </div>

                                <div>
                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                    >
                                        Zapisz opłatę
                                    </button>
                                </div>
                            </form>
                        @endif

                        <div class="mt-5 space-y-4">
                            @forelse ($bills ?? [] as $bill)
                                @php
                                    $sharePerPerson = round($bill->amount / $membersCount, 2);
                                    $currentUserPayment = $bill->payments->firstWhere('user_id', Auth::id());
                                @endphp
                                <div class="rounded-xl border border-slate-800/90 bg-slate-950/60 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800/80 pb-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="text-base font-bold text-white">{{ $bill->title }}</span>
                                            <span class="rounded-md bg-slate-800 px-2 py-0.5 font-mono text-xs text-slate-300">
                                                {{ $bill->billing_month }}
                                            </span>
                                            <span class="text-xs text-slate-400">
                                                Termin: <strong class="text-slate-200">{{ $bill->due_date ? $bill->due_date->format('d.m.Y') : '-' }}</strong>
                                            </span>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3">
                                            <div class="text-right">
                                                <div class="text-sm font-bold text-indigo-300">
                                                    Razem: {{ number_format($bill->amount, 2, ',', ' ') }} zł
                                                </div>
                                                <div class="text-xs text-slate-400">
                                                    Na osobę ({{ $membersCount }}): <strong class="text-white">{{ number_format($sharePerPerson, 2, ',', ' ') }} zł</strong>
                                                </div>
                                            </div>

                                            @if(Auth::user()?->is_admin)
                                                <details class="relative">
                                                    <summary class="cursor-pointer list-none rounded-lg border border-slate-700 bg-slate-800/90 px-2.5 py-1 text-xs font-medium text-slate-200 transition hover:border-indigo-500/50 hover:text-white">
                                                        Edytuj
                                                    </summary>
                                                    <form action="{{ route('flat-bills.update', $bill) }}" method="POST" class="mt-2 flex flex-wrap items-center gap-2 rounded-xl border border-slate-700 bg-slate-950 p-3 shadow-xl">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input
                                                            type="month"
                                                            name="billing_month"
                                                            value="{{ $bill->billing_month }}"
                                                            required
                                                            class="rounded-lg border border-slate-700 bg-slate-900 px-2.5 py-1.5 text-xs text-white"
                                                        >
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0.01"
                                                            name="amount"
                                                            value="{{ $bill->amount }}"
                                                            required
                                                            class="w-28 rounded-lg border border-slate-700 bg-slate-900 px-2.5 py-1.5 text-xs text-white"
                                                        >
                                                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                                                            Zapisz
                                                        </button>
                                                    </form>
                                                </details>

                                                <form action="{{ route('flat-bills.destroy', $bill) }}" method="POST" onsubmit="return confirm('Usunąć ten rachunek?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-rose-500/30 bg-rose-950/30 px-2.5 py-1 text-xs font-medium text-rose-300 transition hover:bg-rose-900/40">
                                                        Usuń
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                                Rozliczenie mieszkańców
                                            </span>

                                            @if(!$isCurrentUserAdmin && (!$currentUserPayment || $currentUserPayment->status === \App\Models\FlatBillPayment::STATUS_PENDING))
                                                <form action="{{ route('flat-bills.submit-payment', $bill) }}" method="POST">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="rounded-lg bg-indigo-600/90 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-500"
                                                    >
                                                        Zgłoś opłacenie ({{ number_format($sharePerPerson, 2, ',', ' ') }} zł)
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach ($bill->payments as $payment)
                                                @php
                                                    $isPaymentOwnerAdmin = isset($admin) && $admin && (int) $payment->user_id === (int) $admin->id;
                                                @endphp
                                                <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-800/80 bg-slate-900/70 px-3 py-2 text-xs">
                                                    <div>
                                                        <div class="font-medium text-white">
                                                            {{ $payment->user->name ?? 'Mieszkaniec' }}
                                                            @if($payment->user_id === Auth::id())
                                                                <span class="text-indigo-400">(Ty)</span>
                                                            @endif
                                                            @if($isPaymentOwnerAdmin)
                                                                <span class="text-slate-400">• admin</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-slate-400">
                                                            Udział: {{ number_format($sharePerPerson, 2, ',', ' ') }} zł
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-col items-end gap-1">
                                                        @if($payment->status === \App\Models\FlatBillPayment::STATUS_PAID || $isPaymentOwnerAdmin)
                                                            <span class="rounded-md border border-emerald-500/30 bg-emerald-950/50 px-2 py-0.5 text-[11px] font-medium text-emerald-300">
                                                                Opłacone
                                                            </span>
                                                        @elseif($payment->status === \App\Models\FlatBillPayment::STATUS_PENDING_APPROVAL)
                                                            <span class="rounded-md border border-sky-500/30 bg-sky-950/50 px-2 py-0.5 text-[11px] font-medium text-sky-300">
                                                                Do sprawdzenia przez admina
                                                            </span>
                                                        @else
                                                            <span class="rounded-md border border-amber-500/30 bg-amber-950/40 px-2 py-0.5 text-[11px] font-medium text-amber-300">
                                                                Do zapłaty
                                                            </span>
                                                        @endif

                                                        @if(Auth::user()?->is_admin && !$isPaymentOwnerAdmin)
                                                            <div class="flex items-center gap-1">
                                                                @if($payment->status !== \App\Models\FlatBillPayment::STATUS_PAID)
                                                                    <form action="{{ route('flat-bill-payments.approve', $payment) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button
                                                                            type="submit"
                                                                            class="rounded border border-emerald-500/30 bg-emerald-600/20 px-2 py-0.5 text-[11px] font-medium text-emerald-200 hover:bg-emerald-600/30"
                                                                        >
                                                                            Zatwierdź
                                                                        </button>
                                                                    </form>
                                                                @endif

                                                                @if($payment->status !== \App\Models\FlatBillPayment::STATUS_PENDING)
                                                                    <form action="{{ route('flat-bill-payments.reset', $payment) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button
                                                                            type="submit"
                                                                            class="rounded border border-slate-700 bg-slate-800 px-2 py-0.5 text-[11px] text-slate-300 hover:bg-slate-700"
                                                                        >
                                                                            Cofnij
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-slate-800/80 bg-slate-950/50 px-4 py-8 text-center text-sm text-slate-400">
                                    Brak dodanych stałych opłat lub rachunków za prąd i gaz.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </details>

                <!-- Rozwijana sekcja 2: Lista wydatków + formularz dodawania -->
                <details class="group rounded-2xl border border-slate-800/90 bg-[#111827]/90 shadow-xl shadow-black/30">
                    <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-4 p-6 transition hover:bg-slate-900/40 rounded-2xl">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-bold text-white">Lista wydatków</h2>
                                <span class="rounded-full border border-slate-700 bg-slate-800/90 px-2.5 py-0.5 text-xs font-semibold text-slate-200">
                                    {{ $expensesCount }} {{ $expensesCount === 1 ? 'wpis' : 'wpisów' }} • {{ number_format($totalAmount, 2, ',', ' ') }} zł
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                Wspólne zakupy i koszty bieżące — kliknij, aby dodać wydatek, przejrzeć listę oraz zatwierdzać spłaty.
                            </p>
                        </div>

                        <span class="rounded-xl border border-slate-700 bg-slate-800/90 px-4 py-2 text-xs font-semibold text-slate-200 transition group-open:border-indigo-500/50 group-open:bg-indigo-600 group-open:text-white">
                            Rozwiń / Zwiń
                        </span>
                    </summary>

                    <div class="border-t border-slate-800/80 p-6 space-y-6">
                        <!-- Formularz dodawania nowego wydatku -->
                        <div class="rounded-xl border border-slate-800/90 bg-slate-950/60 p-5">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-white">Dodaj nowy wydatek</h3>
                            <p class="mt-1 text-xs text-slate-400">
                                Po dodaniu wydatku Twój udział jest od razu oznaczony jako opłacony, a reszta dzieli się na {{ isset($members) ? $members->count() : 1 }} {{ (isset($members) && $members->count() === 1) ? 'osobę' : 'osoby/osób' }}.
                            </p>

                            <form action="{{ route('expenses.store') }}" method="POST" class="mt-4 grid gap-4 md:grid-cols-4 md:items-end">
                                @csrf

                                <div class="md:col-span-2">
                                    <label for="title" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Tytuł wydatku
                                    </label>
                                    <input
                                        type="text"
                                        name="title"
                                        id="title"
                                        value="{{ old('title') }}"
                                        placeholder="np. Zakupy spożywcze"
                                        required
                                        class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                </div>

                                <div>
                                    <label for="amount" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Kwota (zł)
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        name="amount"
                                        id="amount"
                                        value="{{ old('amount') }}"
                                        placeholder="np. 90.00"
                                        required
                                        class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                </div>

                                <div>
                                    <label for="category" class="block text-xs font-medium uppercase tracking-wider text-slate-300">
                                        Kategoria
                                    </label>
                                    @php
                                        $categories = [
                                            'Jedzenie',
                                            'Rachunki',
                                            'Chemia i dom',
                                            'Internet / TV',
                                            'Transport',
                                            'Inne',
                                        ];
                                    @endphp
                                    <select
                                        name="category"
                                        id="category"
                                        required
                                        class="mt-2 w-full rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-2.5 text-sm text-white transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                        <option value="" disabled {{ old('category') ? '' : 'selected' }} class="bg-slate-900 text-slate-400">
                                            Wybierz kategorię...
                                        </option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }} class="bg-slate-900 text-white">
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-4 flex justify-end pt-1">
                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 sm:w-auto"
                                    >
                                        + Dodaj wydatek
                                    </button>
                                </div>
                            </form>
                        </div>

                        <p class="text-xs text-slate-400">
                            Każdy wydatek dzieli się na {{ isset($members) ? $members->count() : 1 }} {{ (isset($members) && $members->count() === 1) ? 'osobę' : 'osoby/osób' }}. Edytować i usuwać może tylko autor wydatku (chyba że autora nie ma już na liście uczestników mieszkania).
                        </p>

                        <div class="space-y-4">
                            @forelse ($expenses ?? [] as $expense)
                                @php
                                    $expenseShare = round($expense->amount / $membersCount, 2);
                                    $isExpenseOwner = (int) $expense->user_id === (int) Auth::id();
                                    $authorStillInFlat = !is_null($expense->user_id) && isset($members) && $members->contains('id', (int) $expense->user_id);
                                    $canDeleteExpense = $isExpenseOwner || !$authorStillInFlat;
                                    $canApproveExpense = $isExpenseOwner || (bool) Auth::user()?->is_admin;
                                    $currentUserExpensePayment = $expense->payments->firstWhere('user_id', Auth::id());
                                @endphp
                                <div class="rounded-xl border border-slate-800/90 bg-slate-950/60 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800/80 pb-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="text-base font-bold text-white">{{ $expense->title }}</span>
                                            <span class="rounded-lg border border-slate-700/80 bg-slate-800/80 px-2.5 py-0.5 text-xs text-slate-200">
                                                {{ $expense->category }}
                                            </span>
                                            <span class="text-xs text-slate-400">
                                                Dodał: <strong class="text-slate-200">{{ $expense->user->name ?? 'Mieszkaniec' }}</strong>
                                            </span>
                                            <span class="text-xs text-slate-500">
                                                {{ $expense->created_at ? $expense->created_at->format('d.m.Y H:i') : '-' }}
                                            </span>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3">
                                            <div class="text-right">
                                                <div class="text-sm font-bold text-indigo-300">
                                                    Razem: {{ number_format($expense->amount, 2, ',', ' ') }} zł
                                                </div>
                                                <div class="text-xs text-slate-400">
                                                    Na osobę ({{ $membersCount }}): <strong class="text-white">{{ number_format($expenseShare, 2, ',', ' ') }} zł</strong>
                                                </div>
                                            </div>

                                            @if($isExpenseOwner)
                                                <details class="relative">
                                                    <summary class="cursor-pointer list-none rounded-lg border border-slate-700 bg-slate-800/90 px-2.5 py-1 text-xs font-medium text-slate-200 transition hover:border-indigo-500/50 hover:text-white">
                                                        Edytuj
                                                    </summary>
                                                    <form action="{{ route('expenses.update', $expense) }}" method="POST" class="mt-2 flex flex-wrap items-center gap-2 rounded-xl border border-slate-700 bg-slate-950 p-3 shadow-xl">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input
                                                            type="text"
                                                            name="title"
                                                            value="{{ $expense->title }}"
                                                            required
                                                            class="w-36 rounded-lg border border-slate-700 bg-slate-900 px-2.5 py-1.5 text-xs text-white"
                                                        >
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0.01"
                                                            name="amount"
                                                            value="{{ $expense->amount }}"
                                                            required
                                                            class="w-24 rounded-lg border border-slate-700 bg-slate-900 px-2.5 py-1.5 text-xs text-white"
                                                        >
                                                        <select
                                                            name="category"
                                                            required
                                                            class="rounded-lg border border-slate-700 bg-slate-900 px-2.5 py-1.5 text-xs text-white"
                                                        >
                                                            @foreach (['Jedzenie', 'Rachunki', 'Chemia i dom', 'Internet / TV', 'Transport', 'Inne'] as $cat)
                                                                <option value="{{ $cat }}" {{ $expense->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                                                            Zapisz
                                                        </button>
                                                    </form>
                                                </details>
                                            @endif

                                            @if($canDeleteExpense)
                                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Usunąć ten wydatek?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-rose-500/30 bg-rose-950/30 px-2.5 py-1 text-xs font-medium text-rose-300 transition hover:bg-rose-900/40">
                                                        Usuń
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                                Rozliczenie wydatku
                                            </span>

                                            @if(!$isExpenseOwner && (!$currentUserExpensePayment || $currentUserExpensePayment->status === \App\Models\ExpensePayment::STATUS_PENDING))
                                                <form action="{{ route('expenses.submit-payment', $expense) }}" method="POST">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="rounded-lg bg-indigo-600/90 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-500"
                                                    >
                                                        Zgłoś spłatę ({{ number_format($expenseShare, 2, ',', ' ') }} zł)
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach ($expense->payments as $payment)
                                                <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-800/80 bg-slate-900/70 px-3 py-2 text-xs">
                                                    <div>
                                                        <div class="font-medium text-white">
                                                            {{ $payment->user->name ?? 'Mieszkaniec' }}
                                                            @if($payment->user_id === Auth::id())
                                                                <span class="text-indigo-400">(Ty)</span>
                                                            @endif
                                                            @if((int) $payment->user_id === (int) $expense->user_id)
                                                                <span class="text-slate-400">• autor</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-slate-400">
                                                            Udział: {{ number_format($expenseShare, 2, ',', ' ') }} zł
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-col items-end gap-1">
                                                        @if($payment->status === \App\Models\ExpensePayment::STATUS_PAID)
                                                            <span class="rounded-md border border-emerald-500/30 bg-emerald-950/50 px-2 py-0.5 text-[11px] font-medium text-emerald-300">
                                                                Opłacone
                                                            </span>
                                                        @elseif($payment->status === \App\Models\ExpensePayment::STATUS_PENDING_APPROVAL)
                                                            <span class="rounded-md border border-sky-500/30 bg-sky-950/50 px-2 py-0.5 text-[11px] font-medium text-sky-300">
                                                                Do sprawdzenia przez admina
                                                            </span>
                                                        @else
                                                            <span class="rounded-md border border-amber-500/30 bg-amber-950/40 px-2 py-0.5 text-[11px] font-medium text-amber-300">
                                                                Do zapłaty
                                                            </span>
                                                        @endif

                                                        @if($canApproveExpense && (int) $payment->user_id !== (int) $expense->user_id)
                                                            <div class="flex items-center gap-1">
                                                                @if($payment->status !== \App\Models\ExpensePayment::STATUS_PAID)
                                                                    <form action="{{ route('expense-payments.approve', $payment) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button
                                                                            type="submit"
                                                                            class="rounded border border-emerald-500/30 bg-emerald-600/20 px-2 py-0.5 text-[11px] font-medium text-emerald-200 hover:bg-emerald-600/30"
                                                                        >
                                                                            Zatwierdź
                                                                        </button>
                                                                    </form>
                                                                @endif

                                                                @if($payment->status !== \App\Models\ExpensePayment::STATUS_PENDING)
                                                                    <form action="{{ route('expense-payments.reset', $payment) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button
                                                                            type="submit"
                                                                            class="rounded border border-slate-700 bg-slate-800 px-2 py-0.5 text-[11px] text-slate-300 hover:bg-slate-700"
                                                                        >
                                                                            Cofnij
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-slate-800/80 bg-slate-950/50 px-4 py-8 text-center text-sm text-slate-400">
                                    Brak dodanych wydatków. Dodaj pierwszy koszt powyżej.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </details>
            </div>
        </div>

        <footer class="mt-10 pt-6 text-center text-xs text-slate-500">
            © {{ date('Y') }} FlatMate. Wszystkie prawa zastrzeżone.
        </footer>
    </div>

    <!-- Wysuwany panel czatu w dolnym prawym rogu -->
    <div class="fixed bottom-5 right-5 z-50 flex flex-col items-end">
        <details id="flat-chat-drawer" class="group" {{ $isChatOpenDefault ? 'open' : '' }}>
            <summary class="flex cursor-pointer list-none items-center gap-2.5 rounded-full border border-indigo-500/40 bg-indigo-600 px-4 py-3 text-xs font-bold text-white shadow-xl shadow-indigo-600/30 transition hover:bg-indigo-500">
                <span>Czat mieszkania</span>
                <span class="rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-semibold">
                    {{ $chatMessages->count() }}
                </span>
            </summary>

            <div class="mt-3 flex h-[26rem] w-80 flex-col overflow-hidden rounded-2xl border border-slate-800 bg-[#111827] shadow-2xl shadow-black/60 sm:w-96">
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-950/80 px-4 py-3">
                    <div>
                        <div class="text-sm font-bold text-white">Czat współlokatorów</div>
                        <div class="text-[11px] text-slate-400">
                            {{ $flat->name ?? 'Mieszkanie' }} • {{ isset($members) ? $members->count() : 1 }} os.
                        </div>
                    </div>
                    <button
                        type="button"
                        onclick="document.getElementById('flat-chat-drawer').removeAttribute('open')"
                        class="rounded-lg border border-slate-800 bg-slate-900 px-2.5 py-1 text-xs text-slate-400 transition hover:text-white"
                    >
                        Zamknij
                    </button>
                </div>

                <div id="flat-chat-scroll" class="flex-1 space-y-2.5 overflow-y-auto p-4">
                    @forelse ($chatMessages as $msg)
                        @php
                            $isMine = (int) $msg->user_id === (int) Auth::id();
                        @endphp
                        <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
                            <div class="mb-0.5 text-[11px] text-slate-400">
                                {{ $isMine ? 'Ty' : ($msg->user->name ?? 'Mieszkaniec') }}
                                <span class="ml-1 text-slate-500">{{ $msg->created_at ? $msg->created_at->format('H:i') : '' }}</span>
                            </div>
                            <div class="max-w-[85%] rounded-2xl px-3.5 py-2 text-xs leading-relaxed {{ $isMine ? 'bg-indigo-600 text-white' : 'border border-slate-800 bg-slate-900/90 text-slate-200' }}">
                                {{ $msg->body }}
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center text-center text-xs text-slate-400">
                            Napisz pierwszą wiadomość do współlokatorów, np. o zakupach lub rachunkach.
                        </div>
                    @endforelse
                </div>

                <form action="{{ route('flat.messages.store') }}" method="POST" class="flex items-center gap-2 border-t border-slate-800 bg-slate-950/80 p-3">
                    @csrf
                    <input
                        type="text"
                        name="body"
                        required
                        maxlength="1000"
                        placeholder="Napisz wiadomość..."
                        autocomplete="off"
                        class="flex-1 rounded-xl border border-slate-700/80 bg-slate-900 px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                    >
                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500"
                    >
                        Wyślij
                    </button>
                </form>
            </div>
        </details>
    </div>

    <script>
        (function () {
            const box = document.getElementById('flat-chat-scroll');
            if (box) {
                box.scrollTop = box.scrollHeight;
            }
        })();
    </script>
</body>
</html>
