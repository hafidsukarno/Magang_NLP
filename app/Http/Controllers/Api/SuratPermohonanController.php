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
            $filePath = $request->file('file')->store('surat_permohonan', 'public');

            // Verify file exists using Storage facade
            if (!Storage::disk('public')->exists($filePath)) {
                throw new \Exception("File tidak tersimpan dengan benar");
            }

            // Get actual file path for OCR
            $fullPath = Storage::disk('public')->path($filePath);

            // Default OCR data
            $universitas   = null;
            $jurusan       = null;
            $programStudi  = null;
            $tanggalMasuk  = null;
            $tanggalKeluar = null;
            $nama          = null;
            $major         = null;
            $type          = 'individual';
            $members       = [];
            $extractedText = '';
            $keahlian      = null;

            // Call OCR service
            try {
                $ocrUrl = config("services.ocr.endpoint", "http://127.0.0.1:5000/extract");
                
                $response = Http::timeout(60)
                    ->attach("file", fopen($fullPath, 'r'), basename($fullPath))
                    ->post($ocrUrl);

                // DEBUG: Catat respon ke log
                Log::info("🔍 OCR Response Status: " . $response->status());
                Log::info("🔍 OCR Response Body: " . $response->body());

                if ($response->successful()) {
                    $ocrResult = $response->json();
                    $info = $ocrResult['data'] ?? [];

                    // Ambil data langsung dari key lowercase yang dikirim Python
                    $universitas   = $info['universitas'] ?? null;
                    $jurusan       = $info['jurusan'] ?? null;
                    $programStudi  = $info['program_studi'] ?? null;
                    $tanggalMasuk  = $info['tanggal_masuk'] ?? null;
                    $tanggalKeluar = $info['tanggal_keluar'] ?? null;
                    
                    // Bersihkan noise "Tidak ditemukan"
                    $cleanVal = function($val) {
                        return ($val === 'Tidak ditemukan' || !$val) ? null : $val;
                    };

                    $universitas   = $cleanVal($universitas);
                    $jurusan       = $cleanVal($jurusan);
                    $programStudi  = $cleanVal($programStudi);
                    $tanggalMasuk  = $cleanVal($tanggalMasuk);
                    $tanggalKeluar = $cleanVal($tanggalKeluar);

                    $mahasiswaList = $info['daftar_mahasiswa'] ?? [];
                    if (is_array($mahasiswaList) && count($mahasiswaList) > 0) {
                        $nama    = $mahasiswaList[0]['Nama'] ?? $mahasiswaList[0]['nama'] ?? null;
                        $members = $mahasiswaList;
                    }

                    $major = $jurusan ?? $programStudi ?? ($members[0]['Prodi'] ?? null);
                    $type  = count($members) > 1 ? 'group' : 'individual';
                    
                    $extractedText = $ocrResult['clean_text'] ?? $ocrResult['raw_text'] ?? $ocrResult['message'] ?? 'Ekstraksi berhasil';
                    $rawText       = $ocrResult['raw_text'] ?? '';

                    // Bersihkan karakter kontrol KECUALI Newline (\n) dan Carriage Return (\r)
                    $extractedText = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $extractedText);
                    $extractedText = mb_convert_encoding($extractedText, 'UTF-8', 'UTF-8');

                    // Pastikan tidak kosong sama sekali
                    if (empty($extractedText)) {
                        $extractedText = "Teks berhasil diekstrak tapi isinya kosong atau gagal diproses oleh Laravel.";
                    }

                    Log::info("🚀 SENDING TO BROWSER:", [
                        'length' => strlen($extractedText),
                        'prefix' => substr($extractedText, 0, 20)
                    ]);
                }

                // Call Skill Extraction Service (Port 5005)
                try {
                    $skillUrl = "http://127.0.0.1:5005/extract-skills/";
                    $skillResponse = Http::timeout(60)
                        ->attach("file", fopen($fullPath, 'r'), basename($fullPath))
                        ->post($skillUrl);

                    if ($skillResponse->successful()) {
                        $skillData = $skillResponse->json();
                        $keahlian = $skillData['keahlian'] ?? '-';
                        Log::info("🔍 Skill Extraction Result: " . $keahlian);
                    } else {
                        $keahlian = '-';
                        Log::warning("⚠️ Skill Extraction failed with status: " . $skillResponse->status());
                    }
                } catch (\Exception $skillError) {
                    Log::error("❌ Skill Service Error: " . $skillError->getMessage());
                    $keahlian = '-';
                }

            } catch (\Exception $ocrError) {
                Log::error("❌ OCR Service Error: " . $ocrError->getMessage());
                $extractedText = "Gagal menghubungi layanan OCR: " . $ocrError->getMessage();
            }

            $finalResponse = [
                'success'        => true,
                'file_path'      => $filePath,
                'extracted_text' => base64_encode((string)$extractedText), // Pakai Base64
                'raw_text'       => (string)($rawText ?? $extractedText),
                'nama'           => $nama,
                'university'     => $universitas,
                'jurusan'        => $jurusan,
                'program_studi'  => $programStudi,
                'major'          => $major,
                'keahlian'       => $keahlian,
                'tanggal_masuk'  => $tanggalMasuk,
                'tanggal_keluar' => $tanggalKeluar,
                'type'           => $type,
                'members'        => $members,
            ];

            return response()->json($finalResponse);

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
            $filePath = $request->file('file')->store('surat_laporan', 'public');

            // Verify file exists
            if (!Storage::disk('public')->exists($filePath)) {
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
