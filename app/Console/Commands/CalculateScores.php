<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Council;
use App\Services\ScoreCalculationService;

class CalculateScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scores:calculate 
                            {--council= : Calculate scores for a specific council ID}
                            {--force : Force calculation even if not all evaluations are complete}
                            {--all : Calculate scores for all active councils}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate evaluation scores for council officers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scoreService = new ScoreCalculationService();
        
        if ($this->option('council')) {
            $this->calculateForCouncil($this->option('council'), $scoreService);
        } elseif ($this->option('all')) {
            $this->calculateForAllCouncils($scoreService);
        } else {
            $this->error('Please specify either --council=ID or --all option');
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Calculate scores for a specific council
     */
    private function calculateForCouncil($councilId, ScoreCalculationService $scoreService)
    {
        $council = Council::find($councilId);
        
        if (!$council) {
            $this->error("Council with ID {$councilId} not found.");
            return;
        }
        
        $this->info("Calculating scores for council: {$council->name}");
        
        if (!$this->option('force') && !$scoreService->canCalculateScores($council)) {
            $this->warn("Not all required evaluations are completed for this council.");
            $this->info("Use --force option to calculate anyway.");
            return;
        }
        
        try {
            $scoreService->calculateCouncilScores($council);
            $this->info("✓ Scores calculated successfully for {$council->name}");
            
            // Display results
            $this->displayResults($council);
            
        } catch (\Exception $e) {
            $this->error("Error calculating scores: " . $e->getMessage());
        }
    }
    
    /**
     * Calculate scores for all active councils
     */
    private function calculateForAllCouncils(ScoreCalculationService $scoreService)
    {
        $councils = Council::where('status', 'active')->get();
        
        if ($councils->isEmpty()) {
            $this->info("No active councils found.");
            return;
        }
        
        $this->info("Found {$councils->count()} active councils");
        
        $calculated = 0;
        $skipped = 0;
        
        foreach ($councils as $council) {
            $this->info("Processing: {$council->name}");
            
            if (!$this->option('force') && !$scoreService->canCalculateScores($council)) {
                $this->warn("  ⚠ Skipping - not all evaluations completed");
                $skipped++;
                continue;
            }
            
            try {
                $scoreService->calculateCouncilScores($council);
                $this->info("  ✓ Scores calculated successfully");
                $calculated++;
                
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
            }
        }
        
        $this->info("\nSummary:");
        $this->info("Calculated: {$calculated}");
        $this->info("Skipped: {$skipped}");
    }
    
    /**
     * Display calculation results for a council
     */
    private function displayResults(Council $council)
    {
        $officers = $council->councilOfficers()
            ->with('student')
            ->orderBy('rank')
            ->get();
            
        if ($officers->isEmpty()) {
            $this->warn("No officers found in this council.");
            return;
        }
        
        $this->info("\nResults:");
        $this->table(
            ['Rank', 'Name', 'Position', 'Self', 'Peer', 'Adviser', 'Final', 'Category'],
            $officers->map(function ($officer) {
                return [
                    $officer->rank ?? '-',
                    $officer->student->first_name . ' ' . $officer->student->last_name,
                    $officer->position_title,
                    $officer->self_score ? number_format($officer->self_score, 2) : '-',
                    $officer->peer_score ? number_format($officer->peer_score, 2) : '-',
                    $officer->adviser_score ? number_format($officer->adviser_score, 2) : '-',
                    $officer->final_score ? number_format($officer->final_score, 2) : '-',
                    $officer->ranking_category ?? '-',
                ];
            })->toArray()
        );
        
        $avgScore = $officers->whereNotNull('final_score')->avg('final_score');
        if ($avgScore) {
            $this->info("Average Final Score: " . number_format($avgScore, 2));
        }
    }
}
