<?php

namespace App\Models;

use App\Models\Support\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Order extends AppModel
{
    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'amount',
        'currency',
        'md5',
        'bill_number',
        'status',
        'paid_at',
        'items',
        'manual_review_status',
        'manual_review_note',
        'manual_review_requested_at',
        'manual_review_resolved_at',
        'manual_review_resolved_by',
        'manual_review_resolution_note',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'manual_review_requested_at' => 'datetime',
        'manual_review_resolved_at' => 'datetime',
        'items' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayProductNameAttribute(): string
    {
        return self::summarizeItems($this->items, $this->product_name);
    }

    public static function summarizeItems(?array $items, ?string $fallback = null): string
    {
        $names = collect($items ?? [])
            ->map(function (mixed $item): string {
                if (!is_array($item)) {
                    return '';
                }

                return trim((string) ($item['name'] ?? ''));
            })
            ->filter()
            ->unique()
            ->values();

        if ($names->isNotEmpty()) {
            return self::buildProductNameSummary($names);
        }

        $fallback = trim((string) $fallback);

        if ($fallback !== '' && strcasecmp($fallback, 'Cart order') !== 0) {
            return mb_substr($fallback, 0, 255);
        }

        return 'N/A';
    }

    private static function buildProductNameSummary(Collection $names): string
    {
        if ($names->count() === 1) {
            return mb_substr((string) $names->first(), 0, 255);
        }

        $summary = $names->take(2)->implode(', ');
        $remaining = $names->count() - 2;

        if ($remaining > 0) {
            $summary .= ' +'.$remaining.' more';
        }

        return mb_substr($summary, 0, 255);
    }

    public static function expirePending(int $seconds = 2000): int
    {
        return static::where('status', 'PENDING')
            ->where('created_at', '<=', now()->subSeconds($seconds))
            ->update(['status' => 'FAILED']);
    }
}
