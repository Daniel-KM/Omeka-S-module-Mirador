<?php declare(strict_types=1);
/**
 * List of js plugins that can be dynamically added to Mirador viewer (last).
 *
 * It is used to have a multiple select field in the site settings page or in a
 * block page. It allows to include automatically the css and the js assets too.
 * The files themselves are included via `view/common/helper/mirador-plugins.phtml`.
 * The options should be set via the json textarea or via the theme.
 *
 * Note:
 * With Mirador 4, plugins are compiled with the bundle. The selection determines
 * which bundle is loaded: vanilla (no plugin), pack (common), or bundle (all).
 */

return [
    'annotations' => 'Annotations',
    'dl' => 'Download files',
    'image-cropper' => 'Image cropper', // @translate
    'image-tools' => 'Image tools',
    'ocr-helper' => 'OCR helper (text panel)', // @translate
    'rotation' => 'Rotation', // @translate
    'ruler' => 'Physical ruler', // @translate
    'share' => 'Share',
    'sync-windows' => 'Synchronize windows', // @translate
    'textoverlay' => 'Text overlay (OCR)', // @translate
    'zoom-percent' => 'Zoom percentage', // @translate
];
