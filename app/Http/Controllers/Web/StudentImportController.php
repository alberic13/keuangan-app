<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\StudentImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportController extends Controller
{
    public function __construct(
        protected StudentImportService $studentImportService,
    ) {
    }

    public function template(): StreamedResponse
    {
        $headers = ['nis', 'nisn', 'full_name', 'class', 'major', 'batch', 'student_type', 'is_active'];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        }, 'template_import_siswa.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function preview(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx'],
        ]);

        $preview = $this->studentImportService->preview($request->file('file'));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Preview import berhasil',
                'data' => $preview,
            ]);
        }

        return back()->with('import_preview', $preview)->with('status', 'Preview import berhasil dibuat.');
    }

    public function commit(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $request->validate([
            'preview_token' => ['required', 'string'],
        ]);

        $result = $this->studentImportService->commit($request->string('preview_token')->toString(), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Import siswa berhasil diproses',
                'data' => $result,
            ], 201);
        }

        return back()->with('status', 'Import siswa berhasil diproses.');
    }
}
