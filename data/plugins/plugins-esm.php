<?php declare(strict_types=1);

/**
 * ESM metadata for each Mirador 4 plugin.
 *
 * Each entry maps a plugin key (matching plugins.php) to its ESM build info:
 * - package: npm package name (used in the import map)
 * - entry:   path to the built ES module (relative to module asset/)
 * - css:     optional path to the plugin CSS (relative to module asset/)
 *
 * The built files are produced by Vite (see build/) and use relative imports
 * to share a single vendor.js chunk (React, MUI, etc.).
 */

return [
    'annotations' => [
        'package' => 'mirador-annotation-editor',
        'entry' => 'vendor/mirador-esm/plugin-annotations.js',
        'css' => 'vendor/mirador-esm/plugin-annotations.css',
    ],
    'dl' => [
        'package' => 'mirador-dl-plugin',
        'entry' => 'vendor/mirador-esm/plugin-dl.js',
    ],
    'image-tools' => [
        'package' => 'mirador-image-tools',
        'entry' => 'vendor/mirador-esm/plugin-image-tools.js',
    ],
    'share' => [
        'package' => 'mirador-share-plugin',
        'entry' => 'vendor/mirador-esm/plugin-share.js',
    ],
];
