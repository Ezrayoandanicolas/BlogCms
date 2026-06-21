<?php

namespace App\Console\Commands;

use App\Models\Backlink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckBacklinks extends Command
{
    protected $signature = 'backlinks:check';
    protected $description = 'Check all active backlinks';

    public function handle()
    {
        Backlink::where('status', 'active')->each(function ($backlink) {
            try {
                $response = Http::timeout(10)->get($backlink->target_url);
                $backlink->logs()->create([
                    'response_code' => $response->status(),
                    'is_live' => $response->successful(),
                    'checked_at' => now(),
                ]);
            } catch (\Exception $e) {
                $backlink->logs()->create([
                    'response_code' => 0,
                    'is_live' => false,
                    'checked_at' => now(),
                ]);
            }
        });

        $this->info('Backlinks checked successfully!');
    }
}
