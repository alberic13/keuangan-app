<?php

namespace App\Livewire;

use App\Models\ImportLog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ImportsPage extends Component
{
    public function render()
    {
        $search = strtolower(trim((string) request('search')));
        $preview = session('import_preview');

        if ($search !== '' && isset($preview['rows']) && is_array($preview['rows'])) {
            $preview['rows'] = collect($preview['rows'])
                ->filter(function (array $row) use ($search) {
                    return str_contains(strtolower(json_encode($row, JSON_UNESCAPED_UNICODE)), $search);
                })
                ->values()
                ->all();
        }

        return view('livewire.imports-page', [
            'preview' => $preview,
            'zipArchiveAvailable' => class_exists(\ZipArchive::class),
            'logs' => ImportLog::query()
                ->with('importer')
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $logQuery) use ($search) {
                        $logQuery
                            ->where('type', 'like', "%{$search}%")
                            ->orWhere('file_name', 'like', "%{$search}%")
                            ->orWhereHas('importer', function (Builder $importerQuery) use ($search) {
                                $importerQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('username', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest()
                ->limit(10)
                ->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Import Siswa',
            'pageHeading' => 'Import Siswa',
            'activeNav' => 'students',
            'currentSubNav' => 'imports',
            'searchPlaceholder' => 'Cari riwayat import...',
        ]);
    }
}
