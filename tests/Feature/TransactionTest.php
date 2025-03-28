<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000.00
        ]);
    }

    public function test_can_create_credit_transaction()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions', [
                'account_id' => $this->account->id,
                'type' => 'Credit',
                'amount' => 500.00,
                'description' => 'Test credit'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'account_id',
                'type',
                'amount',
                'description',
                'created_at'
            ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->account->id,
            'type' => 'Credit',
            'amount' => 500.00
        ]);

        $this->account->refresh();
        $this->assertEquals(1500.00, $this->account->balance);
    }

    public function test_can_create_debit_transaction()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions', [
                'account_id' => $this->account->id,
                'type' => 'Debit',
                'amount' => 300.00,
                'description' => 'Test debit'
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->account->id,
            'type' => 'Debit',
            'amount' => 300.00
        ]);

        $this->account->refresh();
        $this->assertEquals(700.00, $this->account->balance);
    }

    public function test_cannot_create_debit_transaction_with_insufficient_funds()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions', [
                'account_id' => $this->account->id,
                'type' => 'Debit',
                'amount' => 1500.00,
                'description' => 'Test overdraft'
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Insufficient funds'
            ]);

        $this->account->refresh();
        $this->assertEquals(1000.00, $this->account->balance);
    }

    public function test_can_view_transactions()
    {
        Transaction::factory()->count(3)->create([
            'account_id' => $this->account->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?account_id={$this->account->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_filter_transactions_by_date_range()
    {
        Transaction::factory()->count(3)->create([
            'account_id' => $this->account->id,
            'created_at' => now()->subDays(5)
        ]);

        Transaction::factory()->count(2)->create([
            'account_id' => $this->account->id,
            'created_at' => now()->subDays(15)
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?account_id={$this->account->id}&from=" . now()->subDays(10)->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_cannot_view_transactions_of_other_users_account()
    {
        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create([
            'user_id' => $otherUser->id
        ]);

        Transaction::factory()->count(3)->create([
            'account_id' => $otherAccount->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?account_id={$otherAccount->id}");

        $response->assertStatus(403);
    }
} 