<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PositionController extends Controller
{
    public function index(): LengthAwarePaginator
    {
        return Position::query()->latest()->paginate(20);
    }

    public function publicIndex(): \Illuminate\Database\Eloquent\Collection
    {
        return Position::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Position::query()
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->normalizePositionData($this->validatePosition($request));

        $position = Position::create($data);

        AuditLog::log('create', 'position', $position->id, $position->title,
            "Created position '{$position->title}'");

        return response()->json($position, 201);
    }

    public function show(Position $position): Position
    {
        return $position;
    }

    public function update(Request $request, Position $position): Position
    {
        $data = $this->normalizePositionData($this->validatePosition($request, true));

        $position->update($data);

        AuditLog::log('update', 'position', $position->id, $position->title,
            "Updated position '{$position->title}'");

        return $position;
    }

    public function destroy(Position $position): Response
    {
        $title = $position->title;
        $positionId = $position->id;

        $position->delete();

        AuditLog::log('delete', 'position', $positionId, $title,
            "Deleted position '{$title}'");

        return response()->noContent();
    }

    public function toggle(Request $request, Position $position): Position
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $oldStatus = $position->is_active ? 'active' : 'inactive';
        $position->update($data);
        $newStatus = $position->is_active ? 'active' : 'inactive';

        AuditLog::log('update', 'position', $position->id, $position->title,
            "Changed status from {$oldStatus} to {$newStatus}");

        return $position;
    }

    private function validatePosition(Request $request, bool $isUpdate = false): array
    {
        $titleRules    = $isUpdate ? ['sometimes', 'required', 'string', 'max:255'] : ['required', 'string', 'max:255'];
        $locationRules = $isUpdate ? ['sometimes', 'required', 'string', 'max:255'] : ['required', 'string', 'max:255'];

        return $request->validate([
            'title'       => $titleRules,
            'description' => ['nullable', 'string', 'max:4000'],
            'location'    => $locationRules,
            'salary_min'  => ['nullable', 'numeric', 'min:0'],
            'salary_max'  => ['nullable', 'numeric', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
            'status'      => ['sometimes'],
        ]);
    }

    private function normalizePositionData(array $data): array
    {
        if (array_key_exists('status', $data) && ! array_key_exists('is_active', $data)) {
            $data['is_active'] = $this->normalizePositionStatusValue($data['status']);
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = $this->normalizePositionStatusValue($data['is_active']);
        }

        unset($data['status']);

        return $data;
    }

    private function normalizePositionStatusValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return match ($normalized) {
                '1', 'true', 'yes', 'on', 'active', 'enabled' => true,
                '0', 'false', 'no', 'off', 'inactive', 'disabled' => false,
                default => throw ValidationException::withMessages([
                    'status' => 'The status field must be active or inactive.',
                ]),
            };
        }

        throw ValidationException::withMessages([
            'status' => 'The status field must be active or inactive.',
        ]);
    }
}
