{{-- resources/views/transactions/create.blade.php --}}
@extends('layouts.app')
@section('content')
    <h2>Add Transaction</h2>
    <form action="{{ route('transactions.store') }}" method="POST">
        @csrf
        <input type="hidden" name="account_id" value="{{ $account->id }}">
        <div class="mb-3">
            <label>Transaction Type</label>
            <select name="type" class="form-control">
                <option value="Credit">Credit</option>
                <option value="Debit">Debit</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Amount</label>
            <input type="number" name="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description (Optional)</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
