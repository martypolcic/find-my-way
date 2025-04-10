<?php

namespace App\Services;

class TokenBucketRateLimiter
{
    private static $instances = [];
    private $tokens;
    private $lastUpdate;
    private $capacity;
    private $fillRate;

    private function __construct(float $rps, float $burst)
    {
        $this->capacity = $rps + $burst;
        $this->tokens = $this->capacity;
        $this->fillRate = $rps;
        $this->lastUpdate = microtime(true);
    }

    public static function for(string $apiName, float $rps = 10, float $burst = 30): self
    {
        if (!isset(self::$instances[$apiName])) {
            self::$instances[$apiName] = new self($rps, $burst);
        }
        return self::$instances[$apiName];
    }

    public function throttle(): void
    {
        $now = microtime(true);
        $elapsed = $now - $this->lastUpdate;
        $this->lastUpdate = $now;
        
        $this->tokens = min(
            $this->capacity,
            $this->tokens + ($elapsed * $this->fillRate)
        );
        
        if ($this->tokens < 1.0) {
            $wait = (1.0 - $this->tokens) / $this->fillRate;
            usleep((int)($wait * 1000000));
            $this->throttle();
        }
        
        $this->tokens -= 1.0;
    }

    public function getStatus(): array
    {
        return [
            'tokens_remaining' => $this->tokens,
            'fill_rate' => $this->fillRate,
            'capacity' => $this->capacity,
            'estimated_wait' => $this->tokens < 1.0 
                ? (1.0 - $this->tokens) / $this->fillRate
                : 0
        ];
    }
}