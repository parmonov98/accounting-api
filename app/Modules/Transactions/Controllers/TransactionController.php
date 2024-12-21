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
    ) {}

    public function index(IndexTransactionRequest $request): ResourceCollection
    {
        return TransactionResource::collection(
            $this->transactionService->getAllForUser($request->user()->id, $request->validated())
        );
    }

    public function store(StoreTransactionRequest $request): TransactionResource
    {
        $transactionDTO = new TransactionDTO(
            authorId: Auth::id(),
            amount: $request->validated('amount'),
            title: $request->validated('title')
        );

        return TransactionResource::make(
            $this->transactionService->create($transactionDTO)
        );
    }

    public function destroy(int $id): TransactionResource
    {
        return TransactionResource::make(
            $this->transactionService->destroy($id)
        );
    }

    public function summary(): TransactionSummaryResource
    {
        return new TransactionSummaryResource(
            $this->transactionService->getSummary(Auth::id())
        );
    }

    public function balance(): TransactionBalanceResource
    {
        return new TransactionBalanceResource(
            $this->transactionService->getBalance(Auth::id())
        );
    }
}
