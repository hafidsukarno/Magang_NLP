<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Department;
use Carbon\Carbon;

class RpaScoringService
{

    protected $weights = [
        'info_required' => 50,
        'major_match'   => 30,
        'quota_check'   => 20,
    ];

    // SCORING
    public function computeScore(Application $app, array $fields): array
    {

        // SKOR PARSIAL — jika departemen belum dipilih
        if (!$app->department) {

            $importantFields = ['university', 'major', 'purpose'];
            $present = 0;

            foreach ($importantFields as $f) {
                if (!empty($fields[$f])) $present++;
            }

            $infoScore = intval(($present / count($importantFields)) * $this->weights['info_required']);

            return [
                'total' => $infoScore,
                'details' => [
                    'info_required' => $infoScore,
                    'major_match' => 0,
                    'quota_check' => 0,
                    'explanations' => [
                        'info_required' => [
                            'found' => $present,
                            'required' => count($importantFields),
                            'fields' => $importantFields
                        ],
                        'major_match' => 'No department selected',
                        'quota_check' => 'No department selected',
                    ]
                ]
            ];
        }

        // SKOR LENGKAP — jika departemen sudah dipilih
        $details = [
            'info_required' => 0,
            'major_match'   => 0,
            'quota_check'   => 0,
            'explanations'  => []
        ];

        $score = 0;

        // ---- INFO REQUIRED ----
        $importantFields = ['university', 'major', 'purpose'];
        $present = 0;

        foreach ($importantFields as $f) {
            if (!empty($fields[$f])) $present++;
        }

        $infoScore = intval(($present / count($importantFields)) * $this->weights['info_required']);
        $score += $infoScore;

        $details['info_required'] = $infoScore;
        $details['explanations']['info_required'] = [
            'found' => $present,
            'required' => count($importantFields),
            'fields' => $importantFields
        ];

        // ---- MAJOR MATCH ----
        $majorMatchScore = 0;
        $matchFound = null;

        if (!empty($fields['major'])) {

            $majorLower = strtolower($fields['major']);
            $majorLower = str_replace(['.', ',', '/', '\\', '_'], ' ', $majorLower);
            $stopwords = ['dan', '&', 'program', 'studi', 'jurusan', 'teknologi', 'rekayasa', 'fakultas'];

            $tokens = array_values(array_filter(explode(' ', $majorLower), function ($t) use ($stopwords) {
                return strlen($t) > 2 && !in_array($t, $stopwords);
            }));

            $maps = $app->department->prodiMaps ?? [];

            foreach ($maps as $map) {
                $keyword = strtolower($map->prodi_keyword);

                foreach ($tokens as $token) {

                    if (str_contains($token, $keyword) || str_contains($keyword, $token)) {
                        $matchFound = $keyword;
                        $majorMatchScore = $this->weights['major_match'];
                        break 2;
                    }

                    if (levenshtein($token, $keyword) <= 2) {
                        $matchFound = $keyword . " (fuzzy)";
                        $majorMatchScore = $this->weights['major_match'];
                        break 2;
                    }
                }
            }
        }

        $details['major_match'] = $majorMatchScore;
        $details['explanations']['major_match'] = $matchFound ?: 'No match';

        $score += $majorMatchScore;


        // ---- QUOTA CHECK ----
        $targetDeptId = $app->department_id;
        $periodStart = $app->period_start;
        $periodEnd = $app->period_end;
        $currentPeople = $app->type === 'group' ? ($app->members()->count() + 1) : 1;

        if (!$targetDeptId || !$periodStart || !$periodEnd) {
            $quotaValid = false;
            $quotaLeft = 0;
        } else {
            // 1. Get Quota Value (Period-based or Legacy)
            $quotaRecord = \App\Models\DepartmentQuota::where('department_id', $targetDeptId)
                ->where('period_start', '<=', $periodStart)
                ->where('period_end', '>=', $periodEnd)
                ->first();
            
            $quotaValue = $quotaRecord ? (int)$quotaRecord->quota : (int)($app->department->quota ?? 0);

            // 2. Count used slots (Accepted people overlapping the period)
            $acceptedApps = \App\Models\Application::where('department_id', $targetDeptId)
                ->where('status', 'diterima')
                ->where('id', '!=', $app->id)
                ->where(function ($q) use ($periodStart, $periodEnd) {
                    $q->where('period_start', '<=', $periodEnd)
                      ->where('period_end', '>=', $periodStart);
                })
                ->with('members')
                ->get();

            $usedPeople = $acceptedApps->sum(function ($a) {
                return $a->type === 'group' ? ($a->members->count() + 1) : 1;
            });

            $quotaLeft = max(0, $quotaValue - $usedPeople);
            $quotaValid = $quotaLeft >= $currentPeople;
        }

        $quotaScore = $quotaValid ? $this->weights['quota_check'] : 0;

        $details['quota_check'] = $quotaScore;
        $details['explanations']['quota_check'] = [
            'quota_left' => $quotaLeft,
            'group_size' => $currentPeople,
            'valid' => $quotaValid
        ];

        $score += $quotaScore;

        return [
            'total'   => min(100, intval($score)),
            'details' => $details
        ];
    }

    // SCORING UNTUK SEMUA DEPARTEMEN (REKOMENDASI)
    public function simulateForAllDepartments(Application $app, array $fields)
    {
        $departments = Department::all();
        $results = [];

        foreach ($departments as $dept) {

            // Simulasikan application memakai departemen ini
            $appClone = clone $app;
            $appClone->department_id = $dept->id;
            $appClone->department = $dept;

            $score = $this->computeScore($appClone, $fields);

            $results[] = [
                'department_id' => $dept->id,
                'name' => $dept->name,
                'score' => $score['total']
            ];
        }

        // Urutkan skor tertinggi ke rendah
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    // Status Rekomendasi
    public function recommendStatus(Application $app, int $score): string
    {

        if (!$app->department) return 'recommended_pending';

        $targetDeptId = $app->department_id;
        $periodStart = $app->period_start;
        $periodEnd = $app->period_end;
        $currentPeople = $app->type === 'group' ? ($app->members()->count() + 1) : 1;

        // Count used slots overlapping the period
        $acceptedApps = \App\Models\Application::where('department_id', $targetDeptId)
            ->where('status', 'diterima')
            ->where('id', '!=', $app->id)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->where('period_start', '<=', $periodEnd)
                  ->where('period_end', '>=', $periodStart);
            })
            ->with('members')
            ->get();

        $usedPeople = $acceptedApps->sum(function ($a) {
            return $a->type === 'group' ? ($a->members->count() + 1) : 1;
        });

        // Get Quota
        $quotaRecord = \App\Models\DepartmentQuota::where('department_id', $targetDeptId)
            ->where('period_start', '<=', $periodStart)
            ->where('period_end', '>=', $periodEnd)
            ->first();
        
        $quotaValue = $quotaRecord ? (int)$quotaRecord->quota : (int)($app->department->quota ?? 0);

        $quotaLeft = max(0, $quotaValue - $usedPeople);

        if ($quotaLeft < $currentPeople) return 'recommended_pending';

        if ($score >= 80) return 'recommended_diterima';
        if ($score >= 60) return 'recommended_pending';

        return 'recommended_tidak_lolos';
    }
}
