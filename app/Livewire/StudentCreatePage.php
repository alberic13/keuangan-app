<?php

namespace App\Livewire;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Major;
use Livewire\Component;

class StudentCreatePage extends Component
{
    public function render()
    {
        return view('livewire.student-create-page', [
            'batches' => Batch::query()->orderByDesc('academic_year')->get(),
            'classes' => AcademicClass::query()->orderBy('level')->orderBy('name')->get(),
            'majors' => Major::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Tambah Siswa Manual',
            'pageHeading' => 'Tambah Siswa Manual',
            'activeNav' => 'students',
            'currentSubNav' => 'students',
            'searchPlaceholder' => 'Cari siswa atau kembali ke daftar siswa...',
        ]);
    }
}
