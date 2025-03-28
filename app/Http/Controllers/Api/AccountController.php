<?php

namespace App\Http\Controllers\Api;

use App\Helpers\LuhnHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'account_name' => ['required', 'string', 'max:255', Rule::unique('accounts')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user()->id);
                })],
                'account_type' => ['required', 'in:Personal,Business'],
                'currency' => ['required', 'in:USD,EUR,GBP'],
                'initial_balance' => ['nullable', 'numeric', 'min:0'],
            ]);

            $account = Account::create([
                'user_id' => $request->user()->id,
                'account_name' => $validated['account_name'],
                'account_number' => LuhnHelper::generateAccountNumber(),
                'account_type' => $validated['account_type'],
                'currency' => $validated['currency'],
                'balance' => $validated['initial_balance'] ?? 0,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully',
                'data' => $account
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create account',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Request $request, $account_number)
    {
        try {
            $account = Account::where('account_number', $account_number)->firstOrFail();
            
            // Check if the authenticated user owns this account
            if ($request->user()->id !== $account->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to view this account'
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Account details retrieved successfully',
                'data' => $account
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve account details',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $account_number)
    {
        try {
            $account = Account::where('account_number', $account_number)->firstOrFail();
            
            // Check if the authenticated user owns this account
            if ($request->user()->id !== $account->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to update this account'
                ], Response::HTTP_FORBIDDEN);
            }

            $validated = $request->validate([
                'account_name' => ['required', 'string', 'max:255', Rule::unique('accounts')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user()->id);
                })->ignore($account->id)],
                'account_type' => ['required', 'in:Personal,Business'],
                'currency' => ['required', 'in:USD,EUR,GBP'],
            ]);

            $account->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Account updated successfully',
                'data' => $account
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update account',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request, $account_number)
    {
        try {
            $account = Account::where('account_number', $account_number)->firstOrFail();
            
            // Check if the authenticated user owns this account
            if ($request->user()->id !== $account->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to delete this account'
                ], Response::HTTP_FORBIDDEN);
            }

            $account->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Account deactivated successfully'
            ], Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to deactivate account',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 