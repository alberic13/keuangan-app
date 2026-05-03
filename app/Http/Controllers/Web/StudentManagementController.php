<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentManagementController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $data = $request->validate([
            'nis' => ['nullable', 'string', 'max:50', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn'],
            'full_name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'student_type' => ['required', Rule::in(['regular', 'boarding'])],
            'enrollment_date' => ['nullable', 'date'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $student = Student::query()->create($data);
        $this->auditLogs->log('student.created', $student, null, $student->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Data siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $data = $request->validate([
            'nis' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($student->id)],
            'nisn' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nisn')->ignore($student->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'student_type' => ['required', Rule::in(['regular', 'boarding'])],
            'enrollment_date' => ['nullable', 'date'],
            'exit_date' => ['nullable', 'date'],
        ]);

        $data['is_active'] = $request->boolean('is_active', $student->is_active);
        $before = $student->toArray();
        $student->update($data);

        $this->auditLogs->log('student.updated', $student, $before, $student->fresh()->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Data siswa berhasil diperbarui.');
    }

    public function deactivate(Request $request, Student $student): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $before = $student->toArray();
        $student->update([
            'is_active' => false,
            'exit_date' => $student->exit_date ?: now()->toDateString(),
        ]);

        $this->auditLogs->log('student.deactivated', $student, $before, $student->fresh()->toArray(), 'Deactivate siswa', $request->user());

        return $this->redirectBackWithMessage($request, 'Siswa berhasil dinonaktifkan.');
    }

    public function activate(Request $request, Student $student): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $before = $student->toArray();
        $student->update([
            'is_active' => true,
            'exit_date' => null,
        ]);

        $this->auditLogs->log('student.activated', $student, $before, $student->fresh()->toArray(), 'Aktifkan kembali siswa', $request->user());

        return $this->redirectBackWithMessage($request, 'Siswa berhasil diaktifkan kembali.');
    }
}
