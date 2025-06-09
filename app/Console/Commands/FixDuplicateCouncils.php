<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Evaluation;
use Illuminate\Support\Facades\DB;

class FixDuplicateCouncils extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'councils:fix-duplicates {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate councils for the same department and academic year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        // Find duplicate councils
        $duplicates = DB::select('
            SELECT department_id, academic_year, COUNT(*) as count 
            FROM councils 
            GROUP BY department_id, academic_year 
            HAVING COUNT(*) > 1
        ');

        if (empty($duplicates)) {
            $this->info('No duplicate councils found.');
            return 0;
        }

        $this->warn('Found ' . count($duplicates) . ' sets of duplicate councils:');

        foreach ($duplicates as $duplicate) {
            $this->line("Department ID: {$duplicate->department_id}, Academic Year: {$duplicate->academic_year}, Count: {$duplicate->count}");
            
            // Get all councils for this department and academic year
            $councils = Council::where('department_id', $duplicate->department_id)
                ->where('academic_year', $duplicate->academic_year)
                ->with(['department', 'adviser', 'councilOfficers', 'evaluations'])
                ->orderBy('created_at', 'asc')
                ->get();

            $this->table(
                ['ID', 'Name', 'Status', 'Officers', 'Evaluations', 'Created'],
                $councils->map(function ($council) {
                    return [
                        $council->id,
                        $council->name,
                        $council->status,
                        $council->councilOfficers->count(),
                        $council->evaluations->count(),
                        $council->created_at->format('Y-m-d H:i:s')
                    ];
                })
            );

            // Determine which council to keep
            $keepCouncil = $this->determineCouncilToKeep($councils);
            $councilsToRemove = $councils->reject(function ($council) use ($keepCouncil) {
                return $council->id === $keepCouncil->id;
            });

            $this->info("Will keep: Council ID {$keepCouncil->id} ({$keepCouncil->status})");
            
            foreach ($councilsToRemove as $council) {
                $this->warn("Will remove: Council ID {$council->id} ({$council->status})");
                
                if (!$dryRun) {
                    $this->removeCouncil($council);
                }
            }

            $this->line('---');
        }

        if ($dryRun) {
            $this->info('DRY RUN COMPLETE - Run without --dry-run to apply changes');
        } else {
            $this->info('Duplicate councils have been cleaned up.');
        }

        return 0;
    }

    /**
     * Determine which council to keep based on priority rules
     */
    private function determineCouncilToKeep($councils)
    {
        // Priority rules:
        // 1. Keep active council over completed
        // 2. Keep council with more officers
        // 3. Keep council with more evaluations
        // 4. Keep the older council (created first)

        $activeCouncils = $councils->where('status', 'active');
        
        if ($activeCouncils->count() === 1) {
            return $activeCouncils->first();
        }

        if ($activeCouncils->count() > 1) {
            // Multiple active councils, choose by officers count
            $councilsWithOfficers = $activeCouncils->sortByDesc(function ($council) {
                return $council->councilOfficers->count();
            });
            
            return $councilsWithOfficers->first();
        }

        // No active councils, choose from completed ones
        $councilsWithOfficers = $councils->sortByDesc(function ($council) {
            return $council->councilOfficers->count();
        });

        return $councilsWithOfficers->first();
    }

    /**
     * Remove a council and its related data
     */
    private function removeCouncil($council)
    {
        DB::transaction(function () use ($council) {
            // Delete evaluations first
            $council->evaluations()->delete();
            
            // Delete council officers
            $council->councilOfficers()->delete();
            
            // Delete the council
            $council->delete();
            
            $this->info("Removed council ID {$council->id}");
        });
    }
}
