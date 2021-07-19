<?php

/**
 * Change the plugin version everywhere it's necessary.
 *
 * @package LearnyboxMap
 */

$old = '1.0.1';
$new = '1.1.0';
$dir = \dirname(__FILE__) . '/../';

$filesPatterns = [
    'package.json' => [
        "\"version\": \"$old\"" => "\"version\": \"$new\"",
    ],

    'learnyboxmap.php' => [
        "Version:           $old" => "Version:           $new",
        "define( 'LEARNYBOXMAP_VERSION', '$old' );" => "define( 'LEARNYBOXMAP_VERSION', '$new' );",
    ]
];

echo "Change the plugin version from $old to $new:\n";

foreach ($filesPatterns as $file => $patterns) {
    $text = \file_get_contents($dir . $file);

    echo "  → Edit $file\n";

    $text = \str_replace(
        \array_keys($patterns),
        \array_values($patterns),
        $text
    );

    \file_put_contents($dir . $file, $text);
}
