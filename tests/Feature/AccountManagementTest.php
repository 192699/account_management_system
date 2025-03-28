<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Helpers\LuhnHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user and get authentication token
        $this->user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        $this->token = $response->json('token');
    }

    /** @test */
    public function it_can_create_an_account()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/accounts', [
            'account_name' => 'Test Account',
            'account_type' => 'Personal',
            'currency' => 'USD',
            'initial_balance' => 1000
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'account_number',
                    'account_name',
                    'account_type',
                    'currency',
                    'balance',
                    'user_id'
                ]
            ]);

        $this->assertDatabaseHas('accounts', [
            'account_name' => 'Test Account',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_validates_account_number_using_luhn_algorithm()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Test valid account number
        $this->assertTrue(LuhnHelper::validate($account->account_number));

        // Test invalid account number
        $invalidNumber = '1234567890123456';
        $this->assertFalse(LuhnHelper::validate($invalidNumber));
    }

    /** @test */
    public function it_can_create_a_transaction()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/transactions', [
            'account_number' => $account->account_number,
            'type' => 'deposit',
            'amount' => 500,
            'description' => 'Test deposit',
            'date' => now()->format('Y-m-d')
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'account_id',
                    'type',
                    'amount',
                    'description',
                    'date'
                ]
            ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'type' => 'deposit',
            'amount' => 500
        ]);

        // Check if account balance was updated
        $this->assertEquals(1500, $account->fresh()->balance);
    }

    /** @test */
    public function it_prevents_overdraft()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 100
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/transactions', [
            'account_number' => $account->account_number,
            'type' => 'withdrawal',
            'amount' => 200,
            'description' => 'Test withdrawal',
            'date' => now()->format('Y-m-d')
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Insufficient funds'
            ]);

        // Check if account balance remains unchanged
        $this->assertEquals(100, $account->fresh()->balance);
    }

    /** @test */
    public function it_can_list_transactions()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Create some test transactions
        Transaction::factory()->count(3)->create([
            'account_id' => $account->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'account_id',
                        'type',
                        'amount',
                        'description',
                        'date'
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_update_account_details()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->putJson("/api/accounts/{$account->account_number}", [
            'account_name' => 'Updated Account',
            'account_type' => 'Business',
            'currency' => 'EUR'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Account updated successfully'
            ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'account_name' => 'Updated Account',
            'account_type' => 'Business',
            'currency' => 'EUR'
        ]);
    }

    /** @test */
    public function it_can_deactivate_account()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson("/api/accounts/{$account->account_number}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('accounts', [
            'id' => $account->id
        ]);
    }
} 