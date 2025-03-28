<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'from_account' => ['required', 'string', 'exists:accounts,account_number'],
                'to_account' => ['required', 'string', 'exists:accounts,account_number', 'different:from_account'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'description' => ['required', 'string', 'max:255'],
            ]);

            $fromAccount = Account::where('account_number', $validated['from_account'])->firstOrFail();
            $toAccount = Account::where('account_number', $validated['to_account'])->firstOrFail();

            // Check if the authenticated user owns the source account
            if ($request->user()->id !== $fromAccount->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to transfer from this account'
                ], Response::HTTP_FORBIDDEN);
            }

            // Check for sufficient funds
            if ($fromAccount->balance < $validated['amount']) {
                throw ValidationException::withMessages([
                    'amount' => ['Insufficient funds']
                ]);
            }

            DB::beginTransaction();

            // Create withdrawal transaction
            Transaction::create([
                'account_id' => $fromAccount->id,
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
                'description' => "Transfer to {$toAccount->account_number}: {$validated['description']}",
                'date' => now(),
            ]);

            // Create deposit transaction
            Transaction::create([
                'account_id' => $toAccount->id,
                'type' => 'deposit',
                'amount' => $validated['amount'],
                'description' => "Transfer from {$fromAccount->account_number}: {$validated['description']}",
                'date' => now(),
            ]);

            // Update account balances
            $fromAccount->decrement('balance', $validated['amount']);
            $toAccount->increment('balance', $validated['amount']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer completed successfully',
                'data' => [
                    'from_account' => $fromAccount->fresh(),
                    'to_account' => $toAccount->fresh(),
                    'amount' => $validated['amount']
                ]
            ], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Transfer failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 