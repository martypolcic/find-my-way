<?php
namespace App\Integrations;

trait TokenBucketRateLimiter
{
    private float $tokens;
    private float $lastUpdate;
    private float $capacity;
    private float $fillRate;
    private bool $isThrottling = false;

    /**
     * Configure rate limiting
     * @param int $requestsPerSecond - Normal rate limit
     * @param int $burstCapacity - Additional burst capacity
     */
    public function setRateLimit(int $requestsPerSecond, int $burstCapacity = 0): void
    {
        $this->capacity = $requestsPerSecond + $burstCapacity;
        $this->tokens = $this->capacity; // Start with full bucket
        $this->fillRate = $requestsPerSecond;
        $this->lastUpdate = microtime(true);
    }

    /**
     * Enforce rate limiting
     * @throws \RuntimeException When rate limit is exceeded
     */
    protected function throttle(): void
    {
        $now = microtime(true);
        $elapsed = $now - $this->lastUpdate;
        $this->lastUpdate = $now;
        
        // Add new tokens based on elapsed time
        $this->tokens = min(
            $this->capacity,
            $this->tokens + ($elapsed * $this->fillRate)
        );
        
        // Check if we have enough tokens
        if ($this->tokens < 1.0) {
            $this->isThrottling = true;
            $waitTime = (1.0 - $this->tokens) / $this->fillRate;
            usleep((int)($waitTime * 1000000));
            $this->throttle(); // Recursively retry after waiting
            return;
        }
        
        $this->tokens -= 1.0;
        $this->isThrottling = false;
    }

    /**
     * Check current rate limit status
     */
    public function getRateLimitStatus(): array
    {
        return [
            'tokens_remaining' => $this->tokens,
            'fill_rate' => $this->fillRate,
            'capacity' => $this->capacity,
            'is_throttling' => $this->isThrottling,
            'estimated_wait' => $this->tokens < 1.0 
                ? (1.0 - $this->tokens) / $this->fillRate
                : 0
        ];
    }
}