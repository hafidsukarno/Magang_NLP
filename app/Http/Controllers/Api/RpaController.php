<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\RpaResult;
use App\Services\RpaScoringService;
use Illuminate\Support\Facades\Log;

class RpaController extends Controller {

    public function __construct() {
        // you may add middleware to check api token or signature
    }

    public function store(Request $r, RpaScoringService $scoring) {
        $payload = $r->all();

        $app = isset($payload['application_id'])
            ? Application::find($payload['application_id'])
            : null;

        RpaResult::create([
            'application_id' => $app?->id,
            'raw_json'       => $payload,
            'extracted_text' => $payload['raw_text'] ?? null,
            'fields'         => $payload['fields'] ?? null
        ]);

        if ($app) {
            $score = $scoring->computeScore($app, $payload['fields'] ?? []);
            $app->score = $score['total'];
            $app->save();
        }

        return response()->json(['ok' => true]);
    }

}
