#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * FBE PHP Test Runner
 * 
 * Runs all test*.php files and reports results
 * Usage: php run-tests.php
 */

echo "=== FBE PHP Test Runner ===\n\n";

$testFiles = glob(__DIR__ . '/test*.php');
sort($testFiles);

$passed = 0;
$failed = 0;
$skipped = 0;
$failedTests = [];

foreach ($testFiles as $testFile) {
	$testName = basename($testFile);
	
	// Skip test runner itself
	if ($testName === 'run-tests.php') {
		continue;
	}
	
	// Skip cross-platform tests that require Rust binaries
	if (strpos($testName, '_cross') !== false || 
	    in_array($testName, ['test_binary_compat.php', 'test_php_read_rust.php'])) {
		echo "⊘ $testName (skipped - cross-platform/requires Rust)\n";
		$skipped++;
		continue;
	}
	
	echo "→ $testName ... ";
	
	// Run test and capture output
	$output = [];
	$returnCode = 0;
	exec("php " . escapeshellarg($testFile) . " 2>&1", $output, $returnCode);
	
	if ($returnCode === 0) {
		echo "✓ PASS\n";
		$passed++;
	} else {
		echo "✗ FAIL\n";
		$failed++;
		$failedTests[$testName] = implode("\n", $output);
	}
}

echo "\n=== Summary ===\n";
echo "Total: " . ($passed + $failed + $skipped) . " tests\n";
echo "✓ Passed: $passed\n";
echo "✗ Failed: $failed\n";
echo "⊘ Skipped: $skipped\n";

if ($failed > 0) {
	echo "\n=== Failed Tests ===\n";
	foreach ($failedTests as $testName => $output) {
		echo "\n$testName:\n";
		echo str_repeat('-', 60) . "\n";
		echo $output . "\n";
	}
	exit(1);
}

echo "\n✓ All tests passed!\n";
exit(0);

