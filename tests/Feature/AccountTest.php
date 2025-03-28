<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_create_account()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/accounts', [
                'account_name' => 'Test Account',
                'account_type' => 'Personal',
                'currency' => 'USD',
                'initial_balance' => 1000.00
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'account_name',
                'account_number',
                'account_type',
                'currency',
                'balance',
                'created_at',
                'updated_at'
            ]);

        $this->assertDatabaseHas('accounts', [
            'account_name' => 'Test Account',
            'account_type' => 'Personal',
            'currency' => 'USD',
            'balance' => 1000.00
        ]);
    }

    public function test_cannot_create_duplicate_account_name()
    {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'account_name' => 'Test Account'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/accounts', [
                'account_name' => 'Test Account',
                'account_type' => 'Personal',
                'currency' => 'USD'
            ]);

        $response->assertStatus(422);
    }

    public function test_can_view_own_account()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/accounts/{$account->account_number}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $account->id,
                'account_number' => $account->account_number
            ]);
    }

    public function test_cannot_view_other_users_account()
    {
        $otherUser = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/accounts/{$account->account_number}");

        $response->assertStatus(403);
    }

    public function test_can_update_own_account()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/accounts/{$account->account_number}", [
                'account_name' => 'Updated Account',
                'account_type' => 'Business',
                'currency' => 'EUR'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'account_name' => 'Updated Account',
                'account_type' => 'Business',
                'currency' => 'EUR'
            ]);
    }

    public function test_can_deactivate_account()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/accounts/{$account->account_number}");

        $response->assertStatus(204);

        $this->assertSoftDeleted($account);
    }
} 