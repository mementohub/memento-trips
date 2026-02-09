<?php
/**
 * Sync missing translate.* keys into lang file as 'KEY' => 'KEY'.
 * PHP 7+ compatible.
 *
 * Usage:
 *   php tools/sync_translate.php /full/path/to/lang/en/translate.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

if ($argc < 2) {
    fwrite(STDERR, "Usage: php tools/sync_translate.php /path/to/lang/en/translate.php\n");
    exit(1);
}

$langFile = $argv[1];
if (!is_file($langFile)) {
    fwrite(STDERR, "Lang file not found: {$langFile}\n");
    exit(1);
}

$projectRoot = realpath(__DIR__ . '/..');
if (!$projectRoot) {
    fwrite(STDERR, "Cannot resolve project root.\n");
    exit(1);
}

$scanDirs = [
    $projectRoot . '/app',
    $projectRoot . '/resources',
    $projectRoot . '/Modules',
    $projectRoot . '/Cms',
    $projectRoot . '/public',
];

$allowedExt = ['php', 'blade.php', 'js', 'ts', 'jsx', 'tsx', 'vue'];

function endsWith($haystack, $needle) {
    $len = strlen($needle);
    if ($len === 0) return true;
    return substr($haystack, -$len) === $needle;
}

function hasAllowedExt($path, $allowedExt) {
    foreach ($allowedExt as $ext) {
        if (endsWith($path, '.' . $ext)) return true;
    }
    return false;
}

$patterns = [
    '/__\(\s*[\'"]translate\.([^\'"]+)[\'"]\s*[\),]/',    // __('translate.KEY')
    '/@lang\(\s*[\'"]translate\.([^\'"]+)[\'"]\s*\)/',    // @lang('translate.KEY')
    '/trans\(\s*[\'"]translate\.([^\'"]+)[\'"]\s*[\),]/', // trans('translate.KEY')
];

$found = [];
$filesScanned = 0;

foreach ($scanDirs as $dir) {
    if (!is_dir($dir)) continue;

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        if (!$file->isFile()) continue;

        $path = $file->getPathname();
        if (!hasAllowedExt($path, $allowedExt)) continue;

        $content = @file_get_contents($path);
        if ($content === false) continue;

        $filesScanned++;

        foreach ($patterns as $re) {
            if (preg_match_all($re, $content, $m)) {
                foreach ($m[1] as $key) {
                    $key = trim($key);
                    if ($key !== '') $found[$key] = true;
                }
            }
        }
    }
}

$keys = array_keys($found);
sort($keys, SORT_NATURAL);

$existing = require $langFile;
if (!is_array($existing)) {
    fwrite(STDERR, "Lang file does not return an array: {$langFile}\n");
    exit(1);
}

$added = 0;
foreach ($keys as $k) {
    if (!array_key_exists($k, $existing)) {
        $existing[$k] = $k;
        $added++;
    }
}

echo "Scanned files: {$filesScanned}\n";
echo "Found translate.* keys: " . count($keys) . "\n";

if ($added === 0) {
    echo "Nothing to add. All found keys already exist.\n";
    exit(0);
}

$backup = $langFile . '.bak_' . date('Ymd_His');
if (!copy($langFile, $backup)) {
    fwrite(STDERR, "Failed to create backup: {$backup}\n");
    exit(1);
}

ksort($existing, SORT_NATURAL);

$out = "<?php\n\nreturn [\n";
foreach ($existing as $k => $v) {
    $kEsc = str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$k);
    $vEsc = str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$v);
    $out .= "    '{$kEsc}' => '{$vEsc}',\n";
}
$out .= "];\n";

if (file_put_contents($langFile, $out) === false) {
    fwrite(STDERR, "Failed to write lang file: {$langFile}\n");
    exit(1);
}

echo "Added {$added} missing keys to {$langFile}\n";
echo "Backup created: {$backup}\n";
