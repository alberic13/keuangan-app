<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function log(
        string $action,
        Model|string $entity,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
        ?User $actor = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->id ?? auth()->id(),
            'entity_type' => is_string($entity) ? $entity : class_basename($entity),
            'entity_id' => is_string($entity) ? null : $entity->getKey(),
            'action' => $action,
            'reason' => $reason,
            'before_json' => $before,
            'after_json' => $after,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function snapshot(Model $model): array
    {
        return $model->fresh()?->toArray() ?? $model->toArray();
    }
}
