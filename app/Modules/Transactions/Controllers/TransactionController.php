<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Resources\TransactionResource;
use App\Modules\Transactions\Resources\TransactionSummaryResource;
use App\Modules\Transactions\Resources\TransactionBalanceResource;
use Illuminate\Support\Facades\Auth;
use App\Modules\Transactions\Requests\IndexTransactionRequest;
use App\Modules\Transactions\Requests\StoreTransactionRequest;
use App\Modules\Transactions\Services\TransactionService;
use App\Modules\Transactions\DTOs\TransactionDTO;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(
        private readonly TransactionService $transactionService
    ) {
    }

    /**
     * Apply middleware in the routes file instead of constructor
     */

    public function index(IndexTransactionRequest $request): ResourceCollection
    {
        try {
            $userId = $request->user()->id;
            $filters = $request->validated();
            $transactions = $this->transactionService->getAllForUser($userId, $filters);
            return TransactionResource::collection($transactions);
        } catch (\Exception $e) {
            \Log::error('Error getting transactions', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null
            ]);
            throw $e;
        }
    }

    public function store(StoreTransactionRequest $request): TransactionResource
    {
        try {
            $userId = $request->user()->id;
            $validated = $request->validated();

            $transactionDTO = new TransactionDTO(
                authorId: $userId,
                amount: $validated['amount'],
                title: $validated['title']
            );

            $transaction = $this->transactionService->create($transactionDTO);
            return TransactionResource::make($transaction);
        } catch (\Exception $e) {
            \Log::error('Error creating transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null
            ]);
            throw $e;
        }
    }

    public function destroy(int $id): TransactionResource
    {
        return TransactionResource::make(
            $this->transactionService->destroy($id)
        );
    }

    public function summary(): TransactionSummaryResource
    {
        try {
            $userId = auth()->id();
            $summary = $this->transactionService->getSummary($userId);
            return new TransactionSummaryResource($summary);
        } catch (\Exception $e) {
            \Log::error('Error getting transaction summary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id() ?? null
            ]);
            throw $e;
        }
    }

    public function balance(): TransactionBalanceResource
    {
        try {
            $userId = auth()->id();
            $balance = $this->transactionService->getBalance($userId);
            return new TransactionBalanceResource($balance);
        } catch (\Exception $e) {
            \Log::error('Error getting transaction balance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id() ?? null
            ]);
            throw $e;
        }
    }
}
