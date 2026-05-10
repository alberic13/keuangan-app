<?php

namespace App\Livewire;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Major;
use App\Models\Student;
use Livewire\Component;

class StudentsPage extends Component
{
    public function render()
    {
        $students = Student::query()
            ->with(['batch', 'classRoom', 'major'])
            ->when(request('search'), function ($query) {
                $search = (string) request('search');
                $query->where(function ($builder) use ($search) {
                    $builder->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('nis', 'like', '%'.$search.'%')
                        ->orWhere('nisn', 'like', '%'.$search.'%');
                });
            })
            ->when(request('batch_id'), fn ($query) => $query->where('batch_id', request('batch_id')))
            ->when(request('class_id'), fn ($query) => $query->where('class_id', request('class_id')))
            ->when(request('major_id'), fn ($query) => $query->where('major_id', request('major_id')))
            ->when(request('student_type'), fn ($query) => $query->where('student_type', request('student_type')))
            ->latest()
            ->limit(50)
            ->get();

        return view('livewire.students-page', [
            'students' => $students,
            'batches' => Batch::query()->orderByDesc('academic_year')->get(),
            'classes' => AcademicClass::query()->orderBy('level')->orderBy('name')->get(),
            'majors' => Major::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Manajemen Siswa',
            'pageHeading' => 'Manajemen Siswa',
            'activeNav' => 'students',
            'currentSubNav' => 'students',
            'searchPlaceholder' => 'Cari siswa berdasarkan NIS, NISN, atau nama...',
        ]);
    }
}
