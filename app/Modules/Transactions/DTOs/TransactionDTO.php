<?php

declare(strict_types=1);

namespace App\Modules\Transactions\DTOs;

final class TransactionDTO
{
    public function __construct(
        public readonly int $authorId,
        public readonly int $amount,
        public readonly ?string $title = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            authorId: $data['author_id'],
            amount: $data['amount'],
            title: $data['title'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'author_id' => $this->authorId,
            'amount' => $this->amount,
            'title' => $this->title,
        ];
    }
}
