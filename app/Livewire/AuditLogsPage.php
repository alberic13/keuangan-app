<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class AuditLogsPage extends Component
{
    public function render()
    {
        $search = trim((string) request('search'));

        $logs = AuditLog::query()
            ->with('actor')
            ->when(request('actor_id'), fn ($query) => $query->where('actor_id', request('actor_id')))
            ->when(request('entity_type'), fn ($query) => $query->where('entity_type', request('entity_type')))
            ->when(request('action'), fn ($query) => $query->where('action', request('action')))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $logQuery) use ($search) {
                    $logQuery
                        ->where('entity_type', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('entity_id', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('user_agent', 'like', "%{$search}%")
                        ->orWhere('before_json', 'like', "%{$search}%")
                        ->orWhere('after_json', 'like', "%{$search}%")
                        ->orWhereHas('actor', function (Builder $actorQuery) use ($search) {
                            $actorQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->limit(50)
            ->get();

        return view('livewire.audit-logs-page', [
            'logs' => $logs,
        ])->layout('layouts.app', [
            'pageTitle' => 'Log Audit',
            'pageHeading' => 'Transparansi Sistem',
            'activeNav' => 'audit-logs',
            'searchPlaceholder' => 'Cari log audit...',
        ]);
    }
}
