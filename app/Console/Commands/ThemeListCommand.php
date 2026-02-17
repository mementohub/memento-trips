<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Theme;

/**
 * ThemeListCommand
 *
 * Lists all available themes with their metadata.
 * Usage: `php artisan theme:list`
 *
 * @package App\Console\Commands
 */
class ThemeListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available themes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Checking theme directories...");

        // Check possible theme directories
        $possiblePaths = [
            base_path('Cms/themes'),
            base_path('cms/themes'),
            base_path('CMS/themes'),
        ];

        $foundPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_dir($path)) {
                $foundPath = $path;
                $this->info("✓ Theme directory: {$path}");
                break;
            }
        }

        if (!$foundPath) {
            $this->error("✕ No theme directory found.");
            return 1;
        }

        $themes = Theme::all();

        if (empty($themes)) {
            $this->error("No themes found!");
            return 1;
        }

        $activeTheme = Theme::getActive();
        $headers = ['Name', 'Active', 'Version', 'Author', 'Description'];
        $rows = [];

        foreach ($themes as $name => $info) {
            $rows[] = [
                $name,
                $name === $activeTheme ? '✓' : '',
                $info['version'] ?? 'N/A',
                $info['author'] ?? 'N/A',
                $info['description'] ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);

        return 0;
    }
}