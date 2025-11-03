<?php declare(strict_types=1);
/**
 * List of js plugins that can be dynamically added to Mirador viewer (v3.4.3).
 *
 * It is used to have a multiple select field in the site settings page or in a
 * block page. It allows to include automatically the css and the js assets too.
 * The files themselves are included via `view/common/helper/mirador-3-plugins.phtml`.
 * The options should be set via the json textarea or via the theme.
 *
 * Note:
 * Currently, with Mirador 3, plugins should be included and compiled with the
 * bundle, so they are not available separately, but hidden.
 */

return [
    'annotations' => 'Annotations',
    'dl' => 'Download files',
    'image-tools' => 'Image tools',
    'ocr-helper' => 'OCR helper',
    // 'ruler' => 'Ruler',
    'share' => 'Share',
    'textoverlay' => 'Text overlay',
];
