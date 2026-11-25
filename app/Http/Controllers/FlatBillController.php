<?php

namespace App\Http\Controllers;

use App\Models\FlatBill;
use App\Models\FlatBillPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FlatBillController extends Controller
{
    private function syncPaymentsForBill(FlatBill $bill): void
    {
        $memberIds = User::where('flat_id', $bill->flat_id)->pluck('id');

        foreach ($memberIds as $userId) {
            FlatBillPayment::firstOrCreate(
                [
                    'flat_bill_id' => $bill->id,
                    'user_id' => $userId,
                ],
                [
                    'status' => FlatBillPayment::STATUS_PENDING,
                ]
            );
        }

        $this->refreshBillPaidStatus($bill);
    }

    private function refreshBillPaidStatus(FlatBill $bill): void
    {
        $payments = $bill->payments()->get();

        if ($payments->isEmpty()) {
            $bill->update(['is_paid' => false]);
            return;
        }

        $allPaid = $payments->every(fn ($payment) => $payment->status === FlatBillPayment::STATUS_PAID);
        $bill->update(['is_paid' => $allPaid]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id) {
            return redirect()->route('flats.setup');
        }

        if (!$user->is_admin) {
            abort(403, 'Tylko administrator mieszkania może dodawać lub zmieniać stałe opłaty i rachunki.');
        }

        $validated = $request->validate([
            'type' => 'required|in:rent,electricity,gas',
            'amount' => 'required|numeric|min:0.01',
            'billing_month' => 'required|date_format:Y-m',
        ]);

        $titles = [
            'rent' => 'Czynsz',
            'electricity' => 'Prąd',
            'gas' => 'Gaz',
        ];

        $dueDate = Carbon::createFromFormat('Y-m', $validated['billing_month'])
            ->startOfMonth()
            ->addMonth()
            ->day(10)
            ->toDateString();

        $bill = FlatBill::updateOrCreate(
            [
                'flat_id' => $user->flat_id,
                'type' => $validated['type'],
                'billing_month' => $validated['billing_month'],
            ],
            [
                'title' => $titles[$validated['type']],
                'amount' => $validated['amount'],
                'due_date' => $dueDate,
            ]
        );

        $this->syncPaymentsForBill($bill);

        return redirect()->back()->with('success', 'Zapisano opłatę mieszkaniową (termin: ' . Carbon::parse($dueDate)->format('d.m.Y') . ').');
    }

    public function update(Request $request, FlatBill $bill)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id || $bill->flat_id !== $user->flat_id) {
            abort(403);
        }

        if (!$user->is_admin) {
            abort(403, 'Tylko administrator mieszkania może edytować stałe opłaty i rachunki.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'billing_month' => 'required|date_format:Y-m',
        ]);

        $dueDate = Carbon::createFromFormat('Y-m', $validated['billing_month'])
            ->startOfMonth()
            ->addMonth()
            ->day(10)
            ->toDateString();

        $bill->update([
            'amount' => $validated['amount'],
            'billing_month' => $validated['billing_month'],
            'due_date' => $dueDate,
        ]);

        $this->syncPaymentsForBill($bill);

        return redirect()->back()->with('success', 'Zaktualizowano kwotę i termin rachunku (' . Carbon::parse($dueDate)->format('d.m.Y') . ').');
    }

    public function destroy(Request $request, FlatBill $bill)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id || $bill->flat_id !== $user->flat_id) {
            abort(403);
        }

        if (!$user->is_admin) {
            abort(403, 'Tylko administrator mieszkania może usuwać opłaty i rachunki.');
        }

        $bill->delete();

        return redirect()->back()->with('success', 'Usunięto rachunek.');
    }

    // Mieszkaniec zgłasza wpłatę -> status "do sprawdzenia przez administratora"
    public function submitPayment(Request $request, FlatBill $bill)
    {
        $user = $request->user();

        if (!$user || !$user->flat_id || $bill->flat_id !== $user->flat_id) {
            abort(403);
        }

        $payment = FlatBillPayment::firstOrCreate(
            [
                'flat_bill_id' => $bill->id,
                'user_id' => $user->id,
            ],
            [
                'status' => FlatBillPayment::STATUS_PENDING,
            ]
        );

        if ($payment->status !== FlatBillPayment::STATUS_PAID) {
            $payment->update([
                'status' => FlatBillPayment::STATUS_PENDING_APPROVAL,
                'submitted_at' => now(),
            ]);
        }

        $this->refreshBillPaidStatus($bill);

        return redirect()->back()->with('success', 'Zgłoszono wpłatę — oczekuje na sprawdzenie przez administratora.');
    }

    // Administrator akceptuje wpłatę mieszkańca -> status "opłacone"
    public function approvePayment(Request $request, FlatBillPayment $payment)
    {
        $user = $request->user();
        $bill = $payment->bill;

        if (!$user || !$user->flat_id || !$bill || $bill->flat_id !== $user->flat_id || !$user->is_admin) {
            abort(403);
        }

        $payment->update([
            'status' => FlatBillPayment::STATUS_PAID,
            'approved_at' => now(),
        ]);

        $this->refreshBillPaidStatus($bill);

        return redirect()->back()->with('success', 'Zatwierdzono wpłatę jako opłaconą.');
    }

    // Administrator cofa status do "do zapłaty" (np. gdy przelewu jeszcze nie ma na koncie)
    public function resetPayment(Request $request, FlatBillPayment $payment)
    {
        $user = $request->user();
        $bill = $payment->bill;

        if (!$user || !$user->flat_id || !$bill || $bill->flat_id !== $user->flat_id || !$user->is_admin) {
            abort(403);
        }

        $payment->update([
            'status' => FlatBillPayment::STATUS_PENDING,
            'submitted_at' => null,
            'approved_at' => null,
        ]);

        $this->refreshBillPaidStatus($bill);

        return redirect()->back()->with('success', 'Przywrócono status: do zapłaty.');
    }
}
