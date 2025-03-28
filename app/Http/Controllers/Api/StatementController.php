<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StatementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function generate(Request $request, $account_number)
    {
        try {
            $validated = $request->validate([
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            ]);

            $account = Account::where('account_number', $account_number)->firstOrFail();

            // Check if the authenticated user owns this account
            if ($request->user()->id !== $account->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to view this account statement'
                ], Response::HTTP_FORBIDDEN);
            }

            // $transactions = Transaction::where('account_id', $account->id)
            //     ->whereBetween('created_at', [$validated['start_date'], $validated['end_date']])
            //     ->orderBy('created_at', 'desc')
            //     ->get();
            $start_date = Carbon::parse($validated['start_date'])->startOfDay()->toDateTimeString();
            $end_date = Carbon::parse($validated['end_date'])->endOfDay()->toDateTimeString();
            
            $transactions = Transaction::where('account_id', $account->id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->orderBy('created_at', 'desc')
                ->get();
            // dd($transactions);
            $data = [
                'account' => $account,
                'transactions' => $transactions,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'opening_balance' => $this->calculateOpeningBalance($account, $validated['start_date']),
                'closing_balance' => $this->calculateClosingBalance($account, $validated['end_date']),
            ];

            $pdf = Pdf::loadView('statements.account', $data);

            return $pdf->download("statement_{$account->account_number}_{$validated['start_date']}_{$validated['end_date']}.pdf");

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate statement',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function calculateOpeningBalance($account, $startDate)
    {
        // Start with the account's initial balance
        $balance = $account->initial_balance ?? 0;

        // Add all credits before the start date
        $credits = Transaction::where('account_id', $account->id)
            ->where('created_at', '<', $startDate)
            ->where('type', 'Credit')
            ->sum('amount');

        // Subtract all debits before the start date
        $debits = Transaction::where('account_id', $account->id)
            ->where('created_at', '<', $startDate)
            ->where('type', 'Debit')
            ->sum('amount');

        return $balance + $credits - $debits;
    }

    private function calculateClosingBalance($account, $endDate)
    {
        // Start with the account's initial balance
        $balance = $account->initial_balance ?? 0;

        // Add all credits up to the end date
        $credits = Transaction::where('account_id', $account->id)
            ->where('created_at', '<=', $endDate)
            ->where('type', 'Credit')
            ->sum('amount');

        // Subtract all debits up to the end date
        $debits = Transaction::where('account_id', $account->id)
            ->where('created_at', '<=', $endDate)
            ->where('type', 'Debit')
            ->sum('amount');

        return $balance + $credits - $debits;
    }
} 