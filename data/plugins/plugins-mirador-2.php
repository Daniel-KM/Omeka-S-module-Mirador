<?php declare(strict_types=1);
/**
 * List of js plugins that can be dynamically added to Mirador viewer.
 *
 * It is used to have a multiple select field in the site settings page or in a
 * block page. It allows to include automatically the css and the js assets too.
 * The files themselves are included via `view/common/helper/mirador-2-plugins.phtml`.
 * The options should be set via the json textarea or via the theme.
 */

 return [
    'canvas-link' => 'Canvas link',
    'crosslink' => 'Cross link',
    'disable-zoom' => 'Disable zoom',
    'download-menu' => 'Download menu',
    'dragndrop-link' => 'Drag-n-drop link',
    'from-the-page' => 'From the page',
    'from-the-page-collection' => 'From the page (collection)',
    'geojson' => 'Geojson',
    'image-cropper' => 'Image cropper',
    'jump-to-page' => 'Jump to page',
    'keyboard-navigation' => 'Keyboard navigation',
    'ldn' => 'LDN',
    'manifest-button' => 'Manifest button',
    'metadata-panel' => 'Metadata panel',
    'metadata-tab' => 'Metadata tab',
    'multi-page-navigation' => 'Multi-page navigation',
    'physical-ruler' => 'Physical ruler',
    'piwik-tracking' => 'Piwik tracking',
    'ruler' => 'Ruler',
    'share-buttons' => 'Share buttons',
    'share-workspace' => 'Share workspace',
    'sync-windows' => 'Sync windows',
    'view-from-url' => 'View from url',
];
