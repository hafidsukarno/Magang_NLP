<?php
namespace App\Jobs;

use App\Models\Application;
use App\Models\RpaResult;
use App\Services\RpaScoringService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(RpaScoringService $scoring)
    {
        $filePath = storage_path("app/private/" . $this->app->file_path);

        if (!file_exists($filePath)) {
            Log::error("OCR Job: File tidak ditemukan", [
                "application_id" => $this->app->id,
                "path" => $filePath,
            ]);
            return;
        }

        try {
            $start = microtime(true);

            $response = Http::timeout(40)
                ->attach("file", file_get_contents($filePath), basename($filePath))
                ->post(config("services.ocr.endpoint", "http://127.0.0.1:8500/ocr"));

            $duration = round((microtime(true) - $start), 3);

            if (!$response->successful()) {
                Log::error("OCR API gagal", [
                    "application_id" => $this->app->id,
                    "status" => $response->status(),
                    "body" => $response->body(),
                ]);
                return;
            }

        } catch (\Exception $e) {
            Log::error("OCR API Exception", [
                "application_id" => $this->app->id,
                "error" => $e->getMessage()
            ]);
            return;
        }

        $payload = $response->json() ?? [];

        $extractedText = $payload["extracted_text"] ?? "";
        $fields        = $payload["fields"] ?? [];

        RpaResult::create([
            "application_id" => $this->app->id,
            "raw_json"       => $payload,
            "extracted_text" => $extractedText,
            "fields"         => $fields,
            "response_time"  => $duration,
            "status"         => "success",
        ]);

        // Hitung score
        $result = $scoring->computeScore($this->app, $fields);

        $this->app->status = 'pending';
        $this->app->score = $result['total'];
        $this->app->save();

        Log::info("OCR Job selesai", [
            "application_id" => $this->app->id,
            "score_total" => $result['total'],
            "processing_time" => $duration
        ]);
    }
}
