<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Filesystem\Filesystem;
use Drmovi\LaravelModule\Services\ModuleGenerator;

// Create a temporary directory for testing
$testDir = sys_get_temp_dir() . '/laravel-module-test-' . uniqid();
mkdir($testDir, 0755, true);

// Create a test composer.json with custom modules path
$testComposer = [
    'name' => 'test/app',
    'extra' => [
        'laravel-module' => [
            'path' => 'custom-modules'
        ]
    ]
];

file_put_contents($testDir . '/composer.json', json_encode($testComposer, JSON_PRETTY_PRINT));

// Test with custom path
echo "Testing with custom modules path...\n";
$originalBasePath = base_path();

// Mock base_path function temporarily
eval('
function base_path() {
    return "' . $testDir . '";
}
');

$filesystem = new Filesystem();
$generator = new ModuleGenerator($filesystem);

// Use reflection to access protected property
$reflection = new ReflectionClass($generator);
$modulesPathProperty = $reflection->getProperty('modulesPath');
$modulesPathProperty->setAccessible(true);
$modulesPath = $modulesPathProperty->getValue($generator);

echo "Custom modules path: $modulesPath\n";
assert($modulesPath === 'custom-modules', 'Custom path should be custom-modules');

// Test with default path (no config)
echo "\nTesting with default modules path...\n";
unlink($testDir . '/composer.json');

$testComposerDefault = [
    'name' => 'test/app'
];

file_put_contents($testDir . '/composer.json', json_encode($testComposerDefault, JSON_PRETTY_PRINT));

$generator2 = new ModuleGenerator($filesystem);
$modulesPathProperty2 = $reflection->getProperty('modulesPath');
$modulesPathProperty2->setAccessible(true);
$modulesPath2 = $modulesPathProperty2->getValue($generator2);

echo "Default modules path: $modulesPath2\n";
assert($modulesPath2 === 'modules', 'Default path should be modules');

// Cleanup
unlink($testDir . '/composer.json');
rmdir($testDir);

echo "\n✅ All tests passed! Modules path configuration is working correctly.\n";