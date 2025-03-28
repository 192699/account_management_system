<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Log a transaction (Credit/Debit)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'account_id' => ['required', 'exists:accounts,id'],
                'type' => ['required', 'in:Credit,Debit'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'description' => ['nullable', 'string', 'max:255'],
            ]);

            $account = Account::findOrFail($validated['account_id']);
            
            // Check if the authenticated user owns this account
            if ($request->user()->id !== $account->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to perform this transaction'
                ], Response::HTTP_FORBIDDEN);
            }

            // Check for overdraft
            if ($validated['type'] === 'Debit' && $account->balance < $validated['amount']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient funds'
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();

            $transaction = Transaction::create($validated);

            // Update account balance
            $account->balance += $validated['type'] === 'Credit' ? $validated['amount'] : -$validated['amount'];
            $account->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction logged successfully',
                'data' => [
                    'transaction' => $transaction,
                    'account' => [
                        'id' => $account->id,
                        'account_number' => $account->account_number,
                        'balance' => $account->balance
                    ]
                ]
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to log transaction',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get transactions with filters
     */
    public function index(Request $request)
    {
        try {
            // Parse and clean the date inputs
            $from = $request->from ? date('Y-m-d', strtotime($request->from)) : null;
            $to = $request->to ? date('Y-m-d', strtotime($request->to)) : null;

            $validated = $request->validate([
                'account_id' => ['required', 'exists:accounts,id'],
                'from' => ['nullable', 'date'],
                'to' => ['nullable', 'date', 'after_or_equal:from'],
            ]);

            $account = Account::findOrFail($validated['account_id']);
            
            // Check if the authenticated user owns this account
            if ($request->user()->id !== $account->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to view transactions for this account'
                ], Response::HTTP_FORBIDDEN);
            }

            $query = $account->transactions();

            if ($from) {
                $query->whereDate('created_at', '>=', $from);
            }

            if ($to) {
                $query->whereDate('created_at', '<=', $to);
            }

            $transactions = $query->latest()->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Transactions retrieved successfully',
                'data' => [
                    'account' => [
                        'id' => $account->id,
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'balance' => $account->balance,
                    ],
                    'transactions' => $transactions,
                    'filters' => [
                        'from' => $from,
                        'to' => $to,
                    ]
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve transactions',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 