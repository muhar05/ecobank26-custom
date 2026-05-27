<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Str;

class ActivityLogService
{
    protected ?string $correlationId = null;

    /**
     * Get or generate a single correlation ID for the current request context.
     */
    public function getCorrelationId(): string
    {
        if (!$this->correlationId) {
            $this->correlationId = (string) Str::uuid();
        }
        return $this->correlationId;
    }

    /**
     * Set a custom correlation ID (e.g. from an incoming job or request).
     */
    public function setCorrelationId(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }

    /**
     * Log an Info event.
     */
    public function logInfo(string $eventType, string $description, ?array $payload = null): ActivityLog
    {
        return $this->writeLog('info', $eventType, $description, $payload);
    }

    /**
     * Log a Warning event.
     */
    public function logWarning(string $eventType, string $description, ?array $payload = null): ActivityLog
    {
        return $this->writeLog('warning', $eventType, $description, $payload);
    }

    /**
     * Log a Critical event.
     */
    public function logCritical(string $eventType, string $description, ?array $payload = null): ActivityLog
    {
        return $this->writeLog('critical', $eventType, $description, $payload);
    }

    /**
     * Internal writer implementing append-only constraints and sanitization.
     */
    protected function writeLog(string $severity, string $eventType, string $description, ?array $payload): ActivityLog
    {
        $sanitizedPayload = $this->sanitizePayload($payload);

        return ActivityLog::create([
            'user_id' => auth()->id(),
            'severity' => $severity,
            'event_type' => $eventType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'correlation_id' => $this->getCorrelationId(),
            'payload' => $sanitizedPayload,
        ]);
    }

    /**
     * Recursively sanitize sensitive keys in payloads.
     */
    public function sanitizePayload(?array $payload): ?array
    {
        if (empty($payload)) {
            return $payload;
        }

        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'remember_token',
            'token',
            'auth_token',
            'session',
            'session_id',
            'idempotency_token',
            'idempotency_key',
            'secret',
            '_token'
        ];

        $sanitized = [];
        foreach ($payload as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizePayload($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
