{{-- resources/views/accounts/index.blade.php --}}
@extends('layouts.app')
@section('content')
    <h2>Accounts</h2>
    <a href="{{ route('accounts.create') }}" class="btn btn-primary mb-3">Create Account</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Account Name</th>
                <th>Account Number</th>
                <th>Type</th>
                <th>Currency</th>
                <th>Balance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->account_name }}</td>
                    <td>{{ $account->account_number }}</td>
                    <td>{{ $account->account_type }}</td>
                    <td>{{ $account->currency }}</td>
                    <td>${{ $account->balance }}</td>
                    <td>
                        <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('transactions.create', $account->id) }}" class="btn btn-warning btn-sm">Add Transaction</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection