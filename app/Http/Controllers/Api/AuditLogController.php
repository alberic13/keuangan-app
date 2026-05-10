<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->with('actor')
            ->when($request->filled('actor_id'), fn ($query) => $query->where('actor_id', $request->integer('actor_id')))
            ->when($request->filled('entity_type'), fn ($query) => $query->where('entity_type', $request->string('entity_type')))
            ->when($request->filled('entity_id'), fn ($query) => $query->where('entity_id', $request->integer('entity_id')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('created_at')
            ->paginate($request->integer('per_page', 20));

        return $this->success($logs);
    }

    public function show(AuditLog $auditLog)
    {
        return $this->success($auditLog->load('actor'));
    }
}
