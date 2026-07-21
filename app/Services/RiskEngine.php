<?php

namespace App\Services;

class RiskEngine
{
    public function calculate(
        float $weatherRisk,
        float $inflationRisk,
        float $newsRisk,
        float $currencyRisk
    ): array {
        $score = (0.40 * $weatherRisk)
               + (0.30 * $inflationRisk)
               + (0.10 * $newsRisk)
               + (0.20 * $currencyRisk);

        $score = round(min(100, max(0, $score)), 2);

        return [
            'score'          => $score,
            'level'          => $this->classify($score),
            'weather_risk'   => round($weatherRisk, 2),
            'inflation_risk' => round($inflationRisk, 2),
            'news_risk'      => round($newsRisk, 2),
            'currency_risk'  => round($currencyRisk, 2),
        ];
    }

    private function classify(float $score): array
    {
        if ($score < 30) {
            return ['label' => 'Low Risk', 'color' => '#00c851', 'badge' => 'success'];
        }
        if ($score < 60) {
            return ['label' => 'Medium Risk', 'color' => '#ffbb33', 'badge' => 'warning'];
        }
        return ['label' => 'High Risk', 'color' => '#ff4444', 'badge' => 'danger'];
    }
}
