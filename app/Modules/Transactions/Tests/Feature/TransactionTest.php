<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Enums\TransactionType;
use App\Modules\Transactions\Events\TransactionCreated;
use App\Modules\Transactions\Mail\TransactionCreatedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected array $transactions = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Fake events and mail
        Event::fake([TransactionCreated::class]);
        Mail::fake();

        // Create a user with unique email
        $this->user = User::factory()->create([
            'name' => 'Transaction Tester',
            'email' => 'transactions_' . uniqid() . '@example.com',
        ]);

        // Create test transactions within a transaction
        DB::transaction(function () {
            $this->transactions[] = Transaction::create([
                'title' => 'Salary',
                'amount' => 5000,
                'author_id' => $this->user->id,
            ]);

            $this->transactions[] = Transaction::create([
                'title' => 'Rent Payment',
                'amount' => -2000,
                'author_id' => $this->user->id,
            ]);

            $this->transactions[] = Transaction::create([
                'title' => 'Freelance Work',
                'amount' => 1500,
                'author_id' => $this->user->id,
            ]);
        });
    }

    public function test_can_filter_income_transactions(): void
    {
        // Create token and authenticate
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Verify transactions exist
        $this->assertDatabaseCount('transactions', 3);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Salary',
            'amount' => 5000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Rent Payment',
            'amount' => -2000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Freelance Work',
            'amount' => 1500,
            'author_id' => $this->user->id
        ]);

        // Get transactions
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions?type=' . TransactionType::INCOME->value);

        \Log::info('Response', [
            'status' => $response->status(),
            'content' => $response->content(),
            'user_id' => $this->user->id,
            'auth_user' => auth()->user(),
            'token' => $token
        ]);

        $response->assertOk()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data')
                    ->has('links')
                    ->has('meta')
            );

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $transaction) {
            $this->assertGreaterThanOrEqual(0, $transaction['amount']);
        }
    }

    public function test_can_filter_expense_transactions(): void
    {
        // Create token and authenticate
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Verify transactions exist
        $this->assertDatabaseCount('transactions', 3);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Salary',
            'amount' => 5000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Rent Payment',
            'amount' => -2000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Freelance Work',
            'amount' => 1500,
            'author_id' => $this->user->id
        ]);

        // Get transactions
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions?type=' . TransactionType::EXPENSE->value);

        \Log::info('Response', [
            'status' => $response->status(),
            'content' => $response->content(),
            'user_id' => $this->user->id,
            'auth_user' => auth()->user(),
            'token' => $token
        ]);

        $response->assertOk()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data')
                    ->has('links')
                    ->has('meta')
            );

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $transaction) {
            $this->assertLessThan(0, $transaction['amount']);
        }
    }

    public function test_can_filter_by_amount_range(): void
    {
        // Create token and authenticate
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Verify transactions exist
        $this->assertDatabaseCount('transactions', 3);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Salary',
            'amount' => 5000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Rent Payment',
            'amount' => -2000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Freelance Work',
            'amount' => 1500,
            'author_id' => $this->user->id
        ]);

        $minAmount = 1000;
        $maxAmount = 6000;

        // Get transactions
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/transactions?amount_min={$minAmount}&amount_max={$maxAmount}");

        \Log::info('Response', [
            'status' => $response->status(),
            'content' => $response->content(),
            'user_id' => $this->user->id,
            'auth_user' => auth()->user(),
            'token' => $token
        ]);

        $response->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $transaction) {
            $absAmount = abs($transaction['amount']);
            $this->assertGreaterThanOrEqual($minAmount, $absAmount);
            $this->assertLessThanOrEqual($maxAmount, $absAmount);
        }
    }

    public function test_can_filter_by_date_range(): void
    {
        // Create token and authenticate
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Verify transactions exist
        $this->assertDatabaseCount('transactions', 3);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Salary',
            'amount' => 5000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Rent Payment',
            'amount' => -2000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Freelance Work',
            'amount' => 1500,
            'author_id' => $this->user->id
        ]);

        $dateFrom = now()->subDays(7)->toDateString();
        $dateTo = now()->toDateString();

        // Get transactions
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/transactions?date_from={$dateFrom}&date_to={$dateTo}");

        \Log::info('Response', [
            'status' => $response->status(),
            'content' => $response->content(),
            'user_id' => $this->user->id,
            'auth_user' => auth()->user(),
            'token' => $token
        ]);

        $response->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $transaction) {
            $date = substr($transaction['created_at'], 0, 10);
            $this->assertGreaterThanOrEqual($dateFrom, $date);
            $this->assertLessThanOrEqual($dateTo, $date);
        }
    }

    public function test_can_sort_transactions(): void
    {
        // Create token and authenticate
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Verify transactions exist
        $this->assertDatabaseCount('transactions', 3);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Salary',
            'amount' => 5000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Rent Payment',
            'amount' => -2000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Freelance Work',
            'amount' => 1500,
            'author_id' => $this->user->id
        ]);

        // Get transactions
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions?sort[field]=amount&sort[direction]=desc');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $amounts = array_column($data, 'amount');
        $absAmounts = array_map('abs', $amounts);
        $sortedAmounts = $absAmounts;
        rsort($sortedAmounts);
        $this->assertEquals($sortedAmounts, $absAmounts);
    }

    public function test_can_get_transaction_summary(): void
    {
        // Create token and authenticate
        $token = $this->user->createToken('test-token')->plainTextToken;
        $this->actingAs($this->user);

        // Get summary
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions/summary');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_income',
                    'total_expense',
                    'transaction_count'
                ]
            ])
            ->assertJson([
                'data' => [
                    'total_income' => 6500,
                    'total_expense' => 2000,
                    'transaction_count' => 3
                ]
            ]);
    }

    public function test_creating_transaction_triggers_events_and_notifications(): void
    {
        // Create token and authenticate
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Verify transactions exist
        $this->assertDatabaseCount('transactions', 3);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Salary',
            'amount' => 5000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Rent Payment',
            'amount' => -2000,
            'author_id' => $this->user->id
        ]);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Freelance Work',
            'amount' => 1500,
            'author_id' => $this->user->id
        ]);

        $transactionData = [
            'title' => 'New Income',
            'amount' => 1000,
        ];

        // Get transactions
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transactions', $transactionData);

        \Log::info('Response', [
            'status' => $response->status(),
            'content' => $response->content(),
            'user_id' => $this->user->id,
            'auth_user' => auth()->user(),
            'token' => $token
        ]);

        $response->assertCreated();

        // Assert event was dispatched
        Event::assertDispatched(TransactionCreated::class);

        // Assert mail was queued
        Mail::assertQueued(TransactionCreatedMail::class);
    }
}
