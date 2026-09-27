<?php
/**
 * Dependency-free PHP syntax check for custom source.
 */

$roots = array(
	__DIR__ . '/../wp-content/plugins/mba-site-core',
	__DIR__ . '/../wp-content/themes/mba-menuiseries',
);
$failed = false;
$count  = 0;

foreach ( $roots as $root ) {
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root ) );
	foreach ( $iterator as $file ) {
		if ( ! $file->isFile() || 'php' !== $file->getExtension() ) {
			continue;
		}
		++$count;
		$command = escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $file->getPathname() ) . ' 2>&1';
		exec( $command, $output, $status );
		if ( 0 !== $status ) {
			$failed = true;
			fwrite( STDERR, implode( PHP_EOL, $output ) . PHP_EOL );
		}
		$output = array();
	}
}

if ( $failed ) {
	exit( 1 );
}

echo "PHP syntax OK ({$count} files)." . PHP_EOL;
