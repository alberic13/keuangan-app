<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\ImportLog;
use App\Services\StudentImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected StudentImportService $studentImportService,
    ) {
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $this->studentImportService->writeTemplate();
        }, $this->studentImportService->templateFilename(), [
            'Content-Type' => $this->studentImportService->templateContentType(),
        ]);
    }

    public function preview(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        return $this->success(
            $this->studentImportService->preview($request->file('file')),
            'Preview import berhasil',
        );
    }

    public function commit(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $request->validate([
            'preview_token' => ['required', 'string'],
        ]);

        return $this->success(
            $this->studentImportService->commit($request->string('preview_token')->toString(), $request->user()),
            'Import siswa berhasil diproses',
            201,
        );
    }

    public function logs()
    {
        return $this->success(ImportLog::query()->latest()->paginate());
    }

    public function show(ImportLog $importLog)
    {
        return $this->success($importLog->load('rows', 'importer'));
    }
}
