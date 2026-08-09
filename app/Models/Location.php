<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'is_active',
        'average_service_time',
        'max_daily_tickets',
        'open_time',
        'close_time',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'average_service_time' => 'integer',
            'max_daily_tickets' => 'integer',
            'open_time' => 'datetime:H:i',
            'close_time' => 'datetime:H:i',
        ];
    }

    public function counters(): HasMany
    {
        return $this->hasMany(Counter::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeCounters(): HasMany
    {
        return $this->counters()->where('is_active', true);
    }

    public function waitingTickets(): HasMany
    {
        return $this->tickets()->where('status', 'waiting')->orderByDesc('is_priority')->orderBy('created_at');
    }

    public function todayTickets(): HasMany
    {
        return $this->tickets()->whereDate('created_at', today());
    }

    public function generateTicketNumber(): string
    {
        $todayCount = $this->todayTickets()->count() + 1;
        return sprintf('%s-%03d', $this->code, $todayCount);
    }

    public function getCurrentAverageServiceTime(): int
    {
        $recentCompleted = $this->todayTickets()
            ->where('status', 'completed')
            ->whereNotNull('service_time')
            ->latest('completed_at')
            ->take(10)
            ->get();

        if ($recentCompleted->isEmpty()) {
            return $this->average_service_time;
        }

        $averageSeconds = $recentCompleted->avg('service_time');

        // Convert seconds to minutes, minimum 1 minute
        return max(1, (int) round($averageSeconds / 60));
    }

    public function getCurrentAverageServiceTimeForCategory(?int $categoryId): int
    {
        if (is_null($categoryId)) {
            return $this->getCurrentAverageServiceTime();
        }

        $recentCompleted = $this->todayTickets()
            ->where('service_category_id', $categoryId)
            ->where('status', 'completed')
            ->whereNotNull('service_time')
            ->latest('completed_at')
            ->take(10)
            ->get();

        if ($recentCompleted->isEmpty()) {
            return $this->average_service_time;
        }

        $averageSeconds = $recentCompleted->avg('service_time');
        return max(1, (int) round($averageSeconds / 60));
    }

    public function getEstimatedWaitTime(int $position): int
    {
        $activeCounters = $this->activeCounters()->count();
        $avgServiceTime = $this->getCurrentAverageServiceTime();

        if ($activeCounters === 0) {
            return $position * $avgServiceTime;
        }

        return (int) ceil(($position / $activeCounters) * $avgServiceTime);
    }

    public function getEstimatedWaitTimeForCategory(int $position, ?int $categoryId): int
    {
        $activeCounters = $this->activeCounters()->count();
        $avgServiceTime = $this->getCurrentAverageServiceTimeForCategory($categoryId);

        if ($activeCounters === 0) {
            return $position * $avgServiceTime;
        }

        return (int) ceil(($position / $activeCounters) * $avgServiceTime);
    }

    public function isOpen(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->format('H:i');
        return $now >= $this->open_time->format('H:i') && $now <= $this->close_time->format('H:i');
    }

    public function isQueueFull(): bool
    {
        if (is_null($this->max_daily_tickets)) {
            return false;
        }

        return $this->todayTickets()->count() >= $this->max_daily_tickets;
    }
}
