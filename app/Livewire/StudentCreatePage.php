<?php

namespace App\Livewire;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Invoice;
use App\Models\Major;
use App\Models\Student;
use App\Models\StudentType;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class StudentCreatePage extends Component
{
    public string $batchYearLabel = '';

    public string $batchAcademicYear = '';

    public string $className = '';

    public ?string $classLevel = null;

    public string $majorCode = '';

    public string $majorName = '';

    public string $studentTypeLabel = '';

    public function render()
    {
        return view('livewire.student-create-page', [
            'batches' => Batch::query()->orderByDesc('academic_year')->get(),
            'classes' => AcademicClass::query()->orderBy('level')->orderBy('name')->get(),
            'majors' => Major::query()->orderBy('name')->get(),
            'studentTypes' => StudentType::query()->where('is_active', true)->orderBy('label')->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Tambah Siswa Manual',
            'pageHeading' => 'Tambah Siswa Manual',
            'activeNav' => 'students',
            'currentSubNav' => 'students',
            'searchPlaceholder' => 'Cari siswa atau kembali ke daftar siswa...',
        ]);
    }

    public function addBatch(): void
    {
        $data = $this->validate([
            'batchYearLabel' => ['required', 'string', 'max:20', Rule::unique('batches', 'year_label')],
            'batchAcademicYear' => ['required', 'string', 'max:30', Rule::unique('batches', 'academic_year')],
        ]);

        Batch::query()->create([
            'year_label' => $data['batchYearLabel'],
            'academic_year' => $data['batchAcademicYear'],
            'is_active' => true,
        ]);

        $this->reset(['batchYearLabel', 'batchAcademicYear']);
        session()->flash('status', 'Angkatan baru berhasil ditambahkan.');
    }

    public function addClass(): void
    {
        $data = $this->validate([
            'className' => ['required', 'string', 'max:100', Rule::unique('classes', 'name')],
            'classLevel' => ['nullable', 'string', 'max:20'],
        ]);

        AcademicClass::query()->create([
            'name' => $data['className'],
            'level' => $data['classLevel'] ?: null,
            'is_active' => true,
        ]);

        $this->reset(['className', 'classLevel']);
        session()->flash('status', 'Kelas baru berhasil ditambahkan.');
    }

    public function addMajor(): void
    {
        $data = $this->validate([
            'majorCode' => ['required', 'string', 'max:30', Rule::unique('majors', 'code')],
            'majorName' => ['required', 'string', 'max:100', Rule::unique('majors', 'name')],
        ]);

        Major::query()->create([
            'code' => $data['majorCode'],
            'name' => $data['majorName'],
            'is_active' => true,
        ]);

        $this->reset(['majorCode', 'majorName']);
        session()->flash('status', 'Jurusan baru berhasil ditambahkan.');
    }

    public function addStudentType(): void
    {
        $data = $this->validate([
            'studentTypeLabel' => ['required', 'string', 'max:100', Rule::unique('student_types', 'label')],
        ]);

        $slug = Str::slug($data['studentTypeLabel']);

        if ($slug === '' || StudentType::query()->where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'studentTypeLabel' => 'Nama tipe siswa sudah dipakai atau tidak valid.',
            ]);
        }

        StudentType::query()->create([
            'slug' => $slug,
            'label' => $data['studentTypeLabel'],
            'is_active' => true,
        ]);

        $this->reset(['studentTypeLabel']);
        session()->flash('status', 'Tipe siswa baru berhasil ditambahkan.');
    }

    public function deleteBatch(int $batchId): void
    {
        $batch = Batch::query()->findOrFail($batchId);

        if (Student::query()->where('batch_id', $batch->id)->exists()) {
            throw ValidationException::withMessages([
                'batchYearLabel' => 'Angkatan tidak bisa dihapus karena masih dipakai data siswa.',
            ]);
        }

        if (Invoice::query()->whereHas('student', fn ($query) => $query->where('batch_id', $batch->id))->exists()) {
            throw ValidationException::withMessages([
                'batchYearLabel' => 'Angkatan tidak bisa dihapus karena masih dipakai invoice.',
            ]);
        }

        $batch->delete();
        session()->flash('status', 'Angkatan berhasil dihapus.');
    }

    public function deleteClass(int $classId): void
    {
        $class = AcademicClass::query()->findOrFail($classId);

        if (Student::query()->where('class_id', $class->id)->exists()) {
            throw ValidationException::withMessages([
                'className' => 'Kelas tidak bisa dihapus karena masih dipakai data siswa.',
            ]);
        }

        $class->delete();
        session()->flash('status', 'Kelas berhasil dihapus.');
    }

    public function deleteMajor(int $majorId): void
    {
        $major = Major::query()->findOrFail($majorId);

        if (Student::query()->where('major_id', $major->id)->exists()) {
            throw ValidationException::withMessages([
                'majorCode' => 'Jurusan tidak bisa dihapus karena masih dipakai data siswa.',
            ]);
        }

        $major->delete();
        session()->flash('status', 'Jurusan berhasil dihapus.');
    }

    public function deleteStudentType(int $studentTypeId): void
    {
        $studentType = StudentType::query()->findOrFail($studentTypeId);

        if (Student::query()->where('student_type', $studentType->slug)->exists()) {
            throw ValidationException::withMessages([
                'studentTypeLabel' => 'Tipe siswa tidak bisa dihapus karena masih dipakai data siswa.',
            ]);
        }

        $studentType->delete();
        session()->flash('status', 'Tipe siswa berhasil dihapus.');
    }
}
