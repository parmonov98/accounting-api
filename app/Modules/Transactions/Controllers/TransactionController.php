<?php

namespace App\Modules\Transactions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Resources\TransactionResource;
use App\Modules\Transactions\Resources\TransactionSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Modules\Currency\Facades\Currency;
use App\Modules\Transactions\Requests\IndexTransactionRequest;
use App\Modules\Transactions\Requests\StoreTransactionRequest;
use App\Modules\Transactions\Services\TransactionService;
use App\Modules\Transactions\DTOs\TransactionDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(IndexTransactionRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->transactionService->index($request->validated())
        ]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transactionDTO = new TransactionDTO(
            title: $request->validated('title'),
            amount: $request->validated('amount'),
            type: $request->validated('type'),
            authorId: Auth::id()
        );

        return response()->json([
            'data' => $this->transactionService->store($transactionDTO)
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->transactionService->show($id)
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->transactionService->update($id, $request->all())
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->transactionService->destroy($id)
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'data' => new TransactionSummaryResource([
                'income' => Transaction::where('author_id', Auth::id())
                    ->where('type', 'income')
                    ->sum('amount'),
                'expense' => Transaction::where('author_id', Auth::id())
                    ->where('type', 'expense')
                    ->sum('amount')
            ])
        ]);
    }

    public function balance()
    {
        $totalIncome = Transaction::where('author_id', Auth::id())
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalExpense = abs(Transaction::where('author_id', Auth::id())
            ->where('amount', '<', 0)
            ->sum('amount'));

        $balanceEur = $totalIncome - $totalExpense;
        $balanceUsd = Currency::driver()->getRate('EUR', 'USD') * $balanceEur;

        return response()->json([
            'balance' => [
                'EUR' => $balanceEur,
                'USD' => $balanceUsd
            ]
        ]);
    }
}
