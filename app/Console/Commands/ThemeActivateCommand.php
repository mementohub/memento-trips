<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Theme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ThemeActivateCommand
 *
 * Activates a theme by name, updating DB, settings file, and facade.
 * Usage: `php artisan theme:activate theme1`
 *
 * @package App\Console\Commands
 */
class ThemeActivateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:activate {theme : The theme name to activate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate a theme by name';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $themeName = $this->argument('theme');

        // Verify the theme exists
        $themes = Theme::all();

        if (!isset($themes[$themeName])) {
            $this->error("Theme '{$themeName}' not found.");
            $this->info("Available themes: " . implode(', ', array_keys($themes)));
            return 1;
        }

        // Update database
        try {
            if (Schema::hasTable('settings')) {
                DB::table('settings')->updateOrInsert(
                ['key' => 'active_theme'],
                ['value' => $themeName, 'updated_at' => now()]
                );
                $this->info("✓ Database setting updated");
            }
            else {
                $this->warn("⚠ Settings table not found, skipping database update");
            }
        }
        catch (\Exception $e) {
            $this->error("Database error: " . $e->getMessage());
        }

        // Update settings file
        $settingsPath = storage_path('app/theme_settings.json');
        $settings = [];

        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true) ?? [];
        }

        $settings['active_theme'] = $themeName;

        $dir = storage_path('app');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT));
        $this->info("✓ Settings file updated");

        // Set theme via facade
        Theme::set($themeName);
        $this->info("✓ Theme activated: {$themeName}");

        return 0;
    }
}