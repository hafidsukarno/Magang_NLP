<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratPermohonanController extends Controller {

    public function uploadAndScan(Request $request) {
        // 1. Validation
        try {
            $request->validate(['file' => 'required|mimes:pdf|max:10240']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        }

        // 2. Processing
        try {
            $filePath = $request->file('file')->store('surat_permohonan', 'public');
            $fullPath = Storage::disk('public')->path($filePath);

            // Defaults
            $data = [
                'universitas' => null, 'jurusan' => null, 'program_studi' => null,
                'tanggal_masuk' => null, 'tanggal_keluar' => null, 'nama' => null,
                'major' => null, 'type' => 'individual', 'members' => [],
                'extracted_text' => '', 'raw_text' => '', 'keahlian' => null
            ];

            // 3. OCR Call
            try {
                $ocrUrl = config("services.ocr.endpoint", "http://127.0.0.1:5000/extract");
                $response = Http::timeout(300)->attach("file", fopen($fullPath, 'r'), basename($fullPath))->post($ocrUrl);

                if ($response->successful()) {
                    $ocrResult = $response->json();
                    $info = $ocrResult['data'] ?? [];
                    
                    $data['universitas']   = $info['universitas'] ?? null;
                    $data['jurusan']       = $info['jurusan'] ?? null;
                    $data['program_studi']  = $info['program_studi'] ?? null;
                    $data['tanggal_masuk']  = $info['tanggal_masuk'] ?? null;
                    $data['tanggal_keluar'] = $info['tanggal_keluar'] ?? null;
                    
                    $members = $info['daftar_mahasiswa'] ?? [];
                    if (count($members) > 0) {
                        $data['nama']    = $members[0]['Nama'] ?? $members[0]['nama'] ?? null;
                        $data['members'] = $members;
                    }

                    $data['major'] = $data['jurusan'] ?? $data['program_studi'] ?? ($members[0]['Prodi'] ?? null);
                    $data['type']  = count($members) > 1 ? 'group' : 'individual';
                    $data['raw_text'] = $ocrResult['raw_text'] ?? '';
                    
                    $text = $ocrResult['clean_text'] ?? $ocrResult['raw_text'] ?? 'Ekstraksi berhasil';
                    $text = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
                    $data['extracted_text'] = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
                }
            } catch (\Exception $ocrError) {
                Log::error("❌ OCR Service Error: " . $ocrError->getMessage());
                $data['extracted_text'] = "Gagal menghubungi layanan OCR";
            }

            return response()->json([
                'success'        => true,
                'file_path'      => $filePath,
                'extracted_text' => base64_encode((string)$data['extracted_text']),
                'raw_text'       => (string)$data['raw_text'],
                'nama'           => $data['nama'],
                'university'     => $data['universitas'],
                'jurusan'        => $data['jurusan'],
                'program_studi'  => $data['program_studi'],
                'major'          => $data['major'],
                'keahlian'       => $data['keahlian'],
                'tanggal_masuk'  => $data['tanggal_masuk'],
                'tanggal_keluar' => $data['tanggal_keluar'],
                'type'           => $data['type'],
                'members'        => $data['members'],
            ]);

        } catch (\Exception $e) {
            Log::error("Upload Exception: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    public function uploadProposal(Request $request) {
        set_time_limit(0); // Nonaktifkan batas waktu eksekusi PHP
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
            $file = $request->file('file');
            $filePath = $file->store('surat_proposal', 'public');
            $fullPath = storage_path('app/public/' . $filePath);

            // Verify file exists
            if (!file_exists($fullPath)) {
                throw new \Exception("File tidak tersimpan dengan benar di $fullPath");
            }

            // 1. Call General OCR Service (Port 5000)
            $extractedText = "-";
            $rawText = "-";
            try {
                $ocrUrl = "http://127.0.0.1:5000/extract";
                $ocrResponse = Http::timeout(300)
                    ->attach("file", fopen($fullPath, 'r'), basename($fullPath))
                    ->post($ocrUrl);

                if ($ocrResponse->successful()) {
                    $ocrData = $ocrResponse->json();
                    $extractedText = $ocrData['clean_text'] ?? '-';
                    $rawText = $ocrData['raw_text'] ?? '-';
                }
            } catch (\Exception $ocrError) {
                Log::error("❌ OCR Proposal Error: " . $ocrError->getMessage());
            }

            // 2. Call Skill Extraction Service (Port 5005)
            $keahlian = "-";
            $keahlianRawText = "-";
            try {
                $skillUrl = "http://127.0.0.1:5005/extract-skills/";
                $skillResponse = Http::timeout(300)
                    ->attach("file", fopen($fullPath, 'r'), basename($fullPath))
                    ->post($skillUrl);

                if ($skillResponse->successful()) {
                    $skillData = $skillResponse->json();
                    $keahlian = $skillData['keahlian'] ?? '-';
                    // Utamakan clean_text (Hasil Pre-Processing)
                    $keahlianRawText = $skillData['clean_text'] ?? ($skillData['raw_text'] ?? '-');
                }
            } catch (\Exception $skillError) {
                Log::error("❌ Skill Proposal Error: " . $skillError->getMessage());
            }

            return response()->json([
                'success'           => true,
                'file_path'         => $filePath,
                'keahlian'          => $keahlian,
                'extracted_text'    => $keahlianRawText, // This is the cleaned text HRD wants to see
                'message'           => 'Surat proposal berhasil di-upload and di-scan'
            ]);

        } catch (\Exception $e) {
            Log::error("Surat Proposal Upload Exception", [
                "error" => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
