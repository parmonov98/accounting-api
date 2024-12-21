<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Tests\Unit;

use App\Modules\Transactions\Contracts\TransactionRepositoryInterface;
use App\Modules\Transactions\Services\TransactionService;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\DTOs\TransactionDTO;
use App\Modules\Auth\Models\User;
use App\Modules\Transactions\Events\TransactionCreated;
use App\Modules\Transactions\Mail\TransactionCreatedMail;
use App\Modules\Transactions\Exceptions\TransactionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Mockery;

class TransactionServiceTest extends TestCase
{
    protected TransactionRepositoryInterface|MockInterface $repository;
    protected TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        Mail::fake();
        
        $this->repository = Mockery::mock(TransactionRepositoryInterface::class);
        $this->service = new TransactionService($this->repository);
    }

    public function test_get_all_for_user_returns_paginated_results(): void
    {
        // Arrange
        $userId = 1;
        $filters = [];
        $expectedPaginator = new LengthAwarePaginator(
            [new Transaction()],
            1,
            15,
            1
        );

        $this->repository->shouldReceive('getAllForUser')
            ->with($userId, $filters)
            ->andReturn($expectedPaginator);

        // Act
        $result = $this->service->getAllForUser($userId, $filters);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_create_transaction_dispatches_event(): void
    {
        // Arrange
        $dto = new TransactionDTO(
            title: 'Test Transaction',
            amount: 100,
            authorId: 1
        );

        $expectedTransaction = new Transaction();
        $expectedTransaction->title = 'Test Transaction';
        $expectedTransaction->amount = 100;
        $expectedTransaction->author_id = 1;
        $expectedTransaction->exists = true;

        // Create a mock user with required properties
        $mockUser = Mockery::mock(User::class);
        $mockUser->shouldReceive('getAttribute')->with('email')->andReturn('test@example.com');
        $mockUser->shouldReceive('getQueueableRelations')->andReturn([]);
        $mockUser->shouldReceive('getQueueableConnection')->andReturn(null);
        $mockUser->shouldReceive('getQueueableId')->andReturn(1);

        $expectedTransaction->setRelation('author', $mockUser);

        $this->repository->shouldReceive('create')
            ->with(Mockery::on(function ($arg) use ($dto) {
                return $arg instanceof TransactionDTO &&
                    $arg->title === $dto->title &&
                    $arg->amount === $dto->amount &&
                    $arg->authorId === $dto->authorId;
            }))
            ->andReturn($expectedTransaction);

        // Act
        $transaction = $this->service->create($dto);

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        Event::assertDispatched(TransactionCreated::class, function ($event) use ($expectedTransaction) {
            return $event->transaction->is($expectedTransaction);
        });
        Mail::assertQueued(TransactionCreatedMail::class);
    }

    public function test_create_transaction_throws_exception_on_failure(): void
    {
        // Arrange
        $dto = new TransactionDTO(
            title: 'Test Transaction',
            amount: 100,
            authorId: 1
        );

        $this->repository->shouldReceive('create')
            ->with(Mockery::type(TransactionDTO::class))
            ->andThrow(new \Exception('Database error'));

        // Assert & Act
        $this->expectException(TransactionException::class);
        $this->service->create($dto);
    }

    public function test_delete_transaction_returns_true_on_success(): void
    {
        // Arrange
        $transactionId = 1;

        $this->repository->shouldReceive('delete')
            ->with($transactionId)
            ->andReturn(true);

        // Act
        $result = $this->service->delete($transactionId);

        // Assert
        $this->assertTrue($result);
    }

    public function test_delete_transaction_throws_not_found_exception(): void
    {
        // Arrange
        $transactionId = 999;

        $this->repository->shouldReceive('delete')
            ->with($transactionId)
            ->andThrow(new \Exception('Transaction not found'));

        // Assert & Act
        $this->expectException(TransactionException::class);
        $this->service->delete($transactionId);
    }

    public function test_get_summary_returns_array()
    {
        $userId = 1;
        $expectedSummary = [
            'total_income' => 1000,
            'total_expense' => -500,
            'balance' => 500,
            'count' => 5
        ];

        $this->repository->shouldReceive('getSummary')
            ->once()
            ->with($userId, null)
            ->andReturn($expectedSummary);

        $result = $this->service->getSummary($userId);

        $this->assertIsArray($result);
        $this->assertEquals($expectedSummary, $result);
    }

    public function test_create_transaction_sends_email_and_dispatches_event()
    {
        Mail::fake();
        Event::fake();
        
        $user = User::factory()->create();
        $dto = new TransactionDTO(
            authorId: $user->id,
            amount: 100,
            title: 'Test Transaction'
        );
        
        $transaction = Transaction::factory()->make([
            'author_id' => $user->id,
            'amount' => 100,
            'title' => 'Test Transaction'
        ]);
        
        $this->repository->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($transaction);
            
        $result = $this->service->create($dto);
        
        Mail::assertQueued(TransactionCreatedMail::class, function ($mail) use ($user, $transaction) {
            return $mail->hasTo($user->email) && 
                   $mail->transaction->id === $transaction->id;
        });
        
        Event::assertDispatched(TransactionCreated::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id;
        });
        
        $this->assertEquals($transaction->id, $result->id);
    }
}
