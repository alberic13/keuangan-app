<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReferenceController extends Controller
{
    use ApiResponses;

    public function batches()
    {
        return $this->success(Batch::query()->orderByDesc('academic_year')->get());
    }

    public function storeBatch(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate([
            'year_label' => ['required', 'string', 'max:20'],
            'academic_year' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $batch = Batch::query()->create($data + ['is_active' => $request->boolean('is_active', true)]);

        return $this->success($batch, 'Success', 201);
    }

    public function classes()
    {
        return $this->success(AcademicClass::query()->orderBy('level')->orderBy('name')->get());
    }

    public function storeClass(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $class = AcademicClass::query()->create($data + ['is_active' => $request->boolean('is_active', true)]);

        return $this->success($class, 'Success', 201);
    }

    public function majors()
    {
        return $this->success(Major::query()->where('is_active', true)->orderBy('name')->get());
    }

    public function storeMajor(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:majors,code'],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $major = Major::query()->create($data + ['is_active' => $request->boolean('is_active', true)]);

        return $this->success($major, 'Success', 201);
    }
}
