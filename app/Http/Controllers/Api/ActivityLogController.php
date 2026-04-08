<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return $this->errorResponse('Forbidden.', null, 403);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'action' => ['nullable', 'string', 'max:100'],
            'actor_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $limit = (int) ($validated['limit'] ?? 200);
        $limit = max(1, min($limit, 500));

        $query = ActivityLog::query()
            ->with('actor')
            ->latest();

        if (! empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }
        if (! empty($validated['actor_id'])) {
            $query->where('actor_id', $validated['actor_id']);
        }
        if (! empty($validated['subject_id'])) {
            $query->where('subject_id', $validated['subject_id']);
        }

        if (! empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('action', 'like', '%' . $q . '%')
                    ->orWhere('ip', 'like', '%' . $q . '%')
                    ->orWhere('user_agent', 'like', '%' . $q . '%');
            });
        }

        $logs = $query->limit($limit)->get();

        return $this->successResponse(
            'Activity logs retrieved successfully.',
            ActivityLogResource::collection($logs)
        );
    }
}

