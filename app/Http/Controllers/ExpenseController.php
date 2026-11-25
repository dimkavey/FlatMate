<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Flat;
use App\Models\FlatBill;
use App\Models\FlatBillPayment;
use App\Models\FlatMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private function syncPaymentsForExpense(Expense $expense): void
    {
        $memberIds = User::where('flat_id', $expense->flat_id)->pluck('id');

        foreach ($memberIds as $userId) {
            $isCreator = (int) $userId === (int) $expense->user_id;

            ExpensePayment::firstOrCreate(
                [
                    'expense_id' => $expense->id,
                    'user_id' => $userId,
                ],
                [
                    'status' => $isCreator ? ExpensePayment::STATUS_PAID : ExpensePayment::STATUS_PENDING,
                    'approved_at' => $isCreator ? now() : null,
                ]
            );
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->flat_id) {
            return redirect()->route('flats.setup');
        }

        $flat = Flat::find($user->flat_id);
        $members = User::where('flat_id', $user->flat_id)->orderBy('name')->get();
        $admin = $members->firstWhere('is_admin', true);
        $membersCount = max($members->count(), 1);

        $expenses = Expense::with(['user', 'payments.user'])
            ->where('flat_id', $user->flat_id)
            ->latest()
            ->get();

        foreach ($expenses as $expense) {
            $this->syncPaymentsForExpense($expense);
            $expense->load(['user', 'payments.user']);
        }

        $bills = FlatBill::with(['payments.user'])
            ->where('flat_id', $user->flat_id)
            ->orderByDesc('billing_month')
            ->orderBy('type')
            ->get();

        foreach ($bills as $bill) {
            foreach ($members as $member) {
                $isAdminMember = $admin && (int) $member->id === (int) $admin->id;

                FlatBillPayment::firstOrCreate(
                    [
                        'flat_bill_id' => $bill->id,
                        'user_id' => $member->id,
                    ],
                    [
                        'status' => $isAdminMember ? FlatBillPayment::STATUS_PAID : FlatBillPayment::STATUS_PENDING,
                        'approved_at' => $isAdminMember ? now() : null,
                    ]
                );
            }
            $bill->load('payments.user');
        }

        $messages = FlatMessage::with('user')
            ->where('flat_id', $user->flat_id)
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        // Wyliczenie dla zalogowanego użytkownika: ile ma spłacić, komu i do kiedy
        $myDebts = collect();
        $myTotalToPay = 0.0;
        $myTotalPendingApproval = 0.0;

        foreach ($bills as $bill) {
            $isCurrentUserAdminOnBill = $admin && (int) $user->id === (int) $admin->id;
            if ($isCurrentUserAdminOnBill) {
                continue;
            }

            $payment = $bill->payments->firstWhere('user_id', $user->id);
            if ($payment && $payment->status !== FlatBillPayment::STATUS_PAID) {
                $share = round($bill->amount / $membersCount, 2);
                $recipientName = $admin?->name ?? 'Administrator mieszkania';
                $dueText = $bill->due_date ? $bill->due_date->format('d.m.Y') : '10. dzień następnego miesiąca';

                if ($payment->status === FlatBillPayment::STATUS_PENDING) {
                    $myTotalToPay += $share;
                } elseif ($payment->status === FlatBillPayment::STATUS_PENDING_APPROVAL) {
                    $myTotalPendingApproval += $share;
                }

                $myDebts->push([
                    'kind' => 'bill',
                    'title' => $bill->title . ' (' . $bill->billing_month . ')',
                    'amount' => $share,
                    'recipient' => $recipientName,
                    'due' => $dueText,
                    'status' => $payment->status,
                    'action_route' => route('flat-bills.submit-payment', $bill),
                ]);
            }
        }

        foreach ($expenses as $expense) {
            $payment = $expense->payments->firstWhere('user_id', $user->id);
            if ($payment && $payment->status !== ExpensePayment::STATUS_PAID) {
                $share = round($expense->amount / $membersCount, 2);
                $recipientName = $expense->user?->name ?? ($admin?->name ?? 'Autor wydatku');
                $dueText = 'Na bieżąco';

                if ($payment->status === ExpensePayment::STATUS_PENDING) {
                    $myTotalToPay += $share;
                } elseif ($payment->status === ExpensePayment::STATUS_PENDING_APPROVAL) {
                    $myTotalPendingApproval += $share;
                }

                $myDebts->push([
                    'kind' => 'expense',
                    'title' => $expense->title . ' (' . $expense->category . ')',
                    'amount' => $share,
                    'recipient' => $recipientName,
                    'due' => $dueText,
                    'status' => $payment->status,
                    'action_route' => route('expenses.submit-payment', $expense),
                ]);
            }
        }

        // Ile inni mieszkańcy mają jeszcze zwrócić zalogowanemu użytkownikowi
        $othersOweMeTotal = 0.0;
        $othersPendingApprovalToMeTotal = 0.0;

        foreach ($expenses as $expense) {
            if ((int) $expense->user_id === (int) $user->id) {
                $share = round($expense->amount / $membersCount, 2);
                foreach ($expense->payments as $p) {
                    if ((int) $p->user_id !== (int) $user->id) {
                        if ($p->status === ExpensePayment::STATUS_PENDING) {
                            $othersOweMeTotal += $share;
                        } elseif ($p->status === ExpensePayment::STATUS_PENDING_APPROVAL) {
                            $othersPendingApprovalToMeTotal += $share;
                        }
                    }
                }
            }
        }

        $isCurrentUserAdmin = $admin && (int) $user->id === (int) $admin->id;
        if ($isCurrentUserAdmin) {
            foreach ($bills as $bill) {
                $share = round($bill->amount / $membersCount, 2);
                foreach ($bill->payments as $p) {
                    if ((int) $p->user_id !== (int) $user->id) {
                        if ($p->status === FlatBillPayment::STATUS_PENDING) {
                            $othersOweMeTotal += $share;
                        } elseif ($p->status === FlatBillPayment::STATUS_PENDING_APPROVAL) {
                            $othersPendingApprovalToMeTotal += $share;
                        }
                    }
                }
            }
        }

        return view('expenses.index', compact(
            'expenses',
            'bills',
            'flat',
            'members',
            'admin',
            'myDebts',
            'myTotalToPay',
            'myTotalPendingApproval',
            'othersOweMeTotal',
            'othersPendingApprovalToMeTotal',
            'messages'
        ));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id) {
            return redirect()->route('flats.setup');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
        ]);

        $validated['flat_id'] = $user->flat_id;
        $validated['user_id'] = $user->id;

        $expense = Expense::create($validated);
        $this->syncPaymentsForExpense($expense);

        return redirect()->back()->with('success', 'Wydatek został dodany pomyślnie!');
    }

    public function update(Request $request, Expense $expense)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id || $expense->flat_id !== $user->flat_id) {
            abort(403);
        }

        if ((int) $expense->user_id !== (int) $user->id) {
            abort(403, 'Tylko osoba, która dodała ten wydatek, może go edytować.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
        ]);

        $expense->update($validated);
        $this->syncPaymentsForExpense($expense);

        return redirect()->back()->with('success', 'Zaktualizowano wydatek.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id || $expense->flat_id !== $user->flat_id) {
            abort(403);
        }

        $flatMemberIds = User::where('flat_id', $user->flat_id)->pluck('id');
        $authorStillInFlat = !is_null($expense->user_id) && $flatMemberIds->contains((int) $expense->user_id);

        $canDelete = (int) $expense->user_id === (int) $user->id || !$authorStillInFlat;

        if (!$canDelete) {
            abort(403, 'Tylko osoba, która dodała ten wydatek, może go usunąć.');
        }

        $expense->delete();

        return redirect()->back()->with('success', 'Usunięto wydatek.');
    }

    public function submitPayment(Request $request, Expense $expense)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id || $expense->flat_id !== $user->flat_id) {
            abort(403);
        }

        $payment = ExpensePayment::firstOrCreate(
            [
                'expense_id' => $expense->id,
                'user_id' => $user->id,
            ],
            [
                'status' => (int) $user->id === (int) $expense->user_id
                    ? ExpensePayment::STATUS_PAID
                    : ExpensePayment::STATUS_PENDING,
            ]
        );

        if ($payment->status !== ExpensePayment::STATUS_PAID) {
            $payment->update([
                'status' => ExpensePayment::STATUS_PENDING_APPROVAL,
                'submitted_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Zgłoszono spłatę udziału — oczekuje na sprawdzenie.');
    }

    public function approvePayment(Request $request, ExpensePayment $payment)
    {
        $user = $request->user();
        $expense = $payment->expense;

        if (!$user || !$user->flat_id || !$expense || $expense->flat_id !== $user->flat_id) {
            abort(403);
        }

        $canManage = (int) $expense->user_id === (int) $user->id || (bool) $user->is_admin;
        if (!$canManage) {
            abort(403);
        }

        $payment->update([
            'status' => ExpensePayment::STATUS_PAID,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Zatwierdzono spłatę udziału.');
    }

    public function resetPayment(Request $request, ExpensePayment $payment)
    {
        $user = $request->user();
        $expense = $payment->expense;

        if (!$user || !$user->flat_id || !$expense || $expense->flat_id !== $user->flat_id) {
            abort(403);
        }

        $canManage = (int) $expense->user_id === (int) $user->id || (bool) $user->is_admin;
        if (!$canManage) {
            abort(403);
        }

        $payment->update([
            'status' => ExpensePayment::STATUS_PENDING,
            'submitted_at' => null,
            'approved_at' => null,
        ]);

        return redirect()->back()->with('success', 'Przywrócono status: do zapłaty.');
    }
}
