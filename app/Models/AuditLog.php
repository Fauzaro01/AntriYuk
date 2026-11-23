<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'action',
        'description',
        'payload',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public static function log(string $action, string $description, ?array $payload = null): self
    {
        $user = auth()->user();

        return self::create([
            'user_id' => $user?->id,
            'location_id' => $user?->location_id,
            'action' => $action,
            'description' => $description,
            'payload' => $payload,
            'ip_address' => request()->ip(),
        ]);
    }
}
