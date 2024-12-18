<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Tests\Feature;

use Tests\TestCase;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Auth\Models\User;
use App\Modules\Transactions\Enums\TransactionType;
use App\Modules\Transactions\Events\TransactionCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use App\Modules\Transactions\Mail\TransactionCreatedMail;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user with unique email
        $this->user = User::factory()->create([
            'name' => 'Transaction Tester',
            'email' => 'transactions_'.uniqid().'@example.com',
        ]);

        // Create test transactions
        Transaction::create([
            'title' => 'Salary',
            'amount' => 5000,
            'author_id' => $this->user->id,
        ]);

        Transaction::create([
            'title' => 'Rent Payment',
            'amount' => -2000,
            'author_id' => $this->user->id,
        ]);

        Transaction::create([
            'title' => 'Freelance Work',
            'amount' => 1500,
            'author_id' => $this->user->id,
        ]);
    }

    public function test_can_filter_income_transactions(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions?type=' . TransactionType::INCOME->value);

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->has('data.data', fn (AssertableJson $json) =>
                        $json->each(fn (AssertableJson $json) =>
                            $json->where('type', TransactionType::INCOME->value)
                                ->etc()
                        )
                    )
            );
    }

    public function test_can_filter_expense_transactions(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions?type=' . TransactionType::EXPENSE->value);

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->has('data.data', fn (AssertableJson $json) =>
                        $json->each(fn (AssertableJson $json) =>
                            $json->where('type', TransactionType::EXPENSE->value)
                                ->etc()
                        )
                    )
            );
    }

    public function test_can_filter_by_amount_range(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;
        $minAmount = 1000;
        $maxAmount = 3000;

        // Create transactions within the range
        Transaction::factory()->create([
            'amount' => 2000,
            'author_id' => $this->user->id
        ]);
        Transaction::factory()->create([
            'amount' => -2500,
            'author_id' => $this->user->id
        ]);
        // Create transaction outside the range
        Transaction::factory()->create([
            'amount' => 5000,
            'author_id' => $this->user->id
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/transactions?amount_min={$minAmount}&amount_max={$maxAmount}");

        $response->assertOk();

        $data = $response->json();
        dump($data); // Debug the response

        $transactions = $data['data']['data'] ?? [];
        $this->assertNotEmpty($transactions);
        foreach ($transactions as $transaction) {
            $amount = abs($transaction['amount']);
            $this->assertGreaterThanOrEqual($minAmount, $amount);
            $this->assertLessThanOrEqual($maxAmount, $amount);
        }
    }

    public function test_can_filter_by_date_range(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;
        $dateFrom = now()->subDay()->format('Y-m-d');
        $dateTo = now()->addDay()->format('Y-m-d');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/transactions?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertOk();

        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        foreach ($data as $transaction) {
            $createdAt = $transaction['created_at'];
            $this->assertGreaterThanOrEqual($dateFrom, substr($createdAt, 0, 10));
            $this->assertLessThanOrEqual($dateTo, substr($createdAt, 0, 10));
        }
    }

    public function test_can_sort_transactions(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions?sort[field]=amount&sort[direction]=desc');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertIsArray($data['data']);
        $amounts = array_column($data['data'], 'amount');

        // Verify that the amounts are sorted by absolute value in descending order
        $sortedAmounts = $amounts;
        usort($sortedAmounts, function ($a, $b) {
            return abs($b) <=> abs($a);
        });

        $this->assertEquals($sortedAmounts, $amounts, 'Transactions are not sorted correctly by amount');
    }

    public function test_can_get_transaction_summary(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/transactions/summary');

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->whereType('total_income', 'integer')
                        ->whereType('total_expense', 'integer')
                        ->whereType('count', 'integer')
                        ->where('total_income', 6500) // 5000 + 1500
                        ->where('total_expense', 2000) // abs(-2000)
                        ->where('count', 3)
                )
            );
    }

    public function test_creating_transaction_triggers_events_and_notifications(): void
    {
        Event::fake([TransactionCreated::class]);
        Mail::fake();

        $token = $this->user->createToken('test-token')->plainTextToken;
        $transactionData = [
            'title' => 'New Income',
            'amount' => 1000,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transactions', $transactionData);

        $response->assertCreated();

        // Assert event was dispatched
        Event::assertDispatched(TransactionCreated::class);

        // Assert email was sent
        Mail::assertQueued(TransactionCreatedMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }
}
