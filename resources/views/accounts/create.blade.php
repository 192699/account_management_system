{{-- resources/views/accounts/create.blade.php --}}
@extends('layouts.app')
@section('content')
    <h2>Create Account</h2>
    <form action="{{ route('accounts.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Account Name</label>
            <input type="text" name="account_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Account Type</label>
            <select name="account_type" class="form-control">
                <option value="Personal">Personal</option>
                <option value="Business">Business</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Currency</label>
            <select name="currency" class="form-control">
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="GBP">GBP</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Initial Balance (Optional)</label>
            <input type="number" name="balance" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Create Account</button>
    </form>
@endsection