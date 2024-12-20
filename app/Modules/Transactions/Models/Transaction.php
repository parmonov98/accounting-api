<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Transactions\Enums\TransactionType;
use App\Modules\Transactions\Policies\TransactionPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Scope;

//TODO: Nimaga 2 ta model bitta clasda
class AuthorScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $builder->where('author_id', Auth::id());
        }
    }
}

/**
 * @property int $id
 * @property string $title
 * @property float $amount
 * @property int $author_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read User $author
 * @property-read TransactionType $type
 */
class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The policy that authorizes user actions.
     */
    protected static string $policy = TransactionPolicy::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'amount',
        'author_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'type'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new AuthorScope);
    }

    /**
     * Get the author that owns the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Transaction>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getTypeAttribute(): TransactionType
    {
        return TransactionType::fromAmount($this->amount);
    }

    protected static function newFactory()
    {
        return \App\Modules\Transactions\Database\Factories\TransactionFactory::new();
    }
}
