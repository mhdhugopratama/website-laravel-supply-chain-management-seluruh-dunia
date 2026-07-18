<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SentimentLexiconSeeder extends Seeder
{
    public function run(): void
    {
        $pos = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'surge',
            'boost', 'recovery', 'expansion', 'gain', 'rise', 'success',
            'opportunity', 'demand', 'strong', 'robust', 'advance', 'breakthrough',
        ];

        $neg = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'strike',
            'shortage', 'congestion', 'conflict', 'disruption', 'collapse',
            'decline', 'recession', 'tariff', 'sanction', 'loss', 'risk',
            'attack', 'blockage', 'halt', 'suspend', 'ban', 'threat', 'decrease',
        ];

        foreach ($pos as $w) {
            DB::table('positive_words')->insertOrIgnore([
                'word' => $w,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($neg as $w) {
            DB::table('negative_words')->insertOrIgnore([
                'word' => $w,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
