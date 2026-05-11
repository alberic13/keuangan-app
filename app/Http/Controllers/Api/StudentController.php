<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentType;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function index(Request $request)
    {
        $students = Student::query()
            ->with(['batch', 'classRoom', 'major'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search) {
                    $builder->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('nis', 'like', '%'.$search.'%')
                        ->orWhere('nisn', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('batch_id'), fn ($query) => $query->where('batch_id', $request->integer('batch_id')))
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('major_id'), fn ($query) => $query->where('major_id', $request->integer('major_id')))
            ->when($request->filled('student_type'), fn ($query) => $query->where('student_type', $request->string('student_type')))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('full_name')
            ->paginate($request->integer('per_page', 15));

        return $this->success($students);
    }

    public function store(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedData($request);
        $data['is_active'] = $request->boolean('is_active', true);

        $student = Student::query()->create($data);
        $this->auditLogs->log('student.created', $student, null, $student->toArray(), null, $request->user());

        return $this->success($student->load(['batch', 'classRoom', 'major']), 'Success', 201);
    }

    public function show(Student $student)
    {
        return $this->success($student->load(['batch', 'classRoom', 'major', 'invoices.feeType', 'payments.cashAccount']));
    }

    public function update(Request $request, Student $student)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $student->toArray();
        $data = $this->validatedData($request, $student);
        $data['is_active'] = $request->boolean('is_active', $student->is_active);

        $student->update($data);
        $this->auditLogs->log('student.updated', $student, $before, $student->fresh()->toArray(), null, $request->user());

        return $this->success($student->load(['batch', 'classRoom', 'major']));
    }

    public function deactivate(Request $request, Student $student)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $student->toArray();

        $student->update([
            'is_active' => false,
            'exit_date' => $student->exit_date ?: now()->toDateString(),
        ]);

        $this->auditLogs->log('student.deactivated', $student, $before, $student->fresh()->toArray(), 'Deactivate siswa', $request->user());

        return $this->success($student, 'Success');
    }

    protected function validatedData(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'nis' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($student?->id)],
            'nisn' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nisn')->ignore($student?->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'student_type' => ['required', Rule::in(StudentType::activeSlugs())],
            'enrollment_date' => ['nullable', 'date'],
            'exit_date' => ['nullable', 'date'],
        ]);
    }
}
