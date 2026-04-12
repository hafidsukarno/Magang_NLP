<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratPermohonanController extends Controller {

    public function uploadAndScan(Request $request) {
        // Validate with proper error handling
        try {
            $validated = $request->validate([
                'file' => 'required|mimes:pdf|max:10240',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Save file
            $filePath = $request->file('file')->store('surat_permohonan');

            // Verify file exists using Storage facade
            if (!Storage::disk('local')->exists($filePath)) {
                throw new \Exception("File tidak tersimpan dengan benar");
            }

            // Get actual file path for OCR
            $fullPath = Storage::disk('local')->path($filePath);

            // Default OCR data (jika service down, tetap return success dengan data null)
            $nama = null;
            $major = null;
            $type = null;
            $extractedText = '';

            // Call OCR service (optional - jika gagal, tetap lanjut)
            try {
                $response = Http::timeout(40)
                    ->attach("file", fopen($fullPath, 'r'), basename($fullPath))
                    ->post(config("services.ocr.endpoint", "http://127.0.0.1:8500/ocr"));

                if ($response->successful()) {
                    $ocrData = $response->json() ?? [];

                    // Extract nama, major, type dari OCR
                    $nama = $ocrData['fields']['nama'] ?? $ocrData['fields']['name'] ?? null;
                    $major = $ocrData['fields']['major'] ?? $ocrData['fields']['program_studi'] ?? $ocrData['fields']['prodi'] ?? null;
                    $type = $ocrData['fields']['type'] ?? $ocrData['fields']['tipe'] ?? null;
                    $extractedText = $ocrData['extracted_text'] ?? '';
                } else {
                    Log::warning("OCR API return non-200", [
                        "status" => $response->status(),
                        "body" => $response->body(),
                    ]);
                }
            } catch (\Exception $ocrError) {
                Log::warning("OCR service error (non-blocking)", [
                    "error" => $ocrError->getMessage()
                ]);
                // Continue regardless - OCR is optional
            }

            return response()->json([
                'success' => true,
                'file_path' => $filePath,
                'extracted_text' => $extractedText,
                'nama' => $nama,
                'major' => $major,
                'type' => $type,
            ]);

        } catch (\Exception $e) {
            Log::error("Surat Permohonan Upload Exception", [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadLaporan(Request $request) {
        // Validate with proper error handling
        try {
            $validated = $request->validate([
                'file' => 'required|mimes:pdf|max:10240',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Save file
            $filePath = $request->file('file')->store('surat_laporan');

            // Verify file exists
            if (!Storage::disk('local')->exists($filePath)) {
                throw new \Exception("File tidak tersimpan dengan benar");
            }

            return response()->json([
                'success' => true,
                'file_path' => $filePath,
                'message' => 'Surat laporan berhasil di-upload'
            ]);

        } catch (\Exception $e) {
            Log::error("Surat Laporan Upload Exception", [
                "error" => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
