<?php declare(strict_types=1);

namespace Mirador\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\Theme\Theme;

class Mirador extends AbstractHelper
{
    /**
     * @var Theme The current theme, if any
     */
    protected $currentTheme;

    /**
     * @var string "2" or "3" (version of Mirador).
     */
    protected $version;

    /**
     * @var AbstractResourceEntityRepresentation|AbstractResourceEntityRepresentation[] $resource
     */
    protected $resource;

    /**
     * Construct the helper.
     *
     * @param Theme|null $currentTheme
     */
    public function __construct(Theme $currentTheme = null)
    {
        $this->currentTheme = $currentTheme;
    }

    /**
     * Get the Mirador Viewer for the provided resource.
     *
     * Proxies to {@link render()}.
     *
     * @param AbstractResourceEntityRepresentation|AbstractResourceEntityRepresentation[] $resource
     * @param array $options
     * @return string Html string corresponding to the viewer.
     */
    public function __invoke($resource, $options = []): string
    {
        if (empty($resource)) {
            return '';
        }
        $this->resource = $resource;

        $view = $this->getView();

        // If the manifest is not provided in metadata, point to the manifest
        // created from Omeka files only when the Iiif Server is installed.
        $iiifServerIsActive = $view->getHelperPluginManager()->has('iiifUrl');

        $isCollection = is_array($resource);

        // Prepare the url of the manifest for a dynamic collection.
        if ($isCollection) {
            if (!$iiifServerIsActive) {
                return '';
            }
            $urlManifest = $view->iiifUrl($resource);
            return $this->render($urlManifest, $options, 'multiple');
        }

        // Prepare the url for the manifest of a record after additional checks.
        $resourceName = $resource->resourceName();
        if (!in_array($resourceName, ['items', 'item_sets'])) {
            return '';
        }

        // Determine the url of the manifest from a field in the metadata.
        $externalManifest = $view->iiifManifestExternal($resource);
        if ($externalManifest) {
            return $this->render($externalManifest, $options, $resourceName, true);
        }

        // If the manifest is not provided in metadata, point to the manifest
        // created from Omeka files if the module Iiif Server is enabled.
        if (!$iiifServerIsActive) {
            return '';
        }

        // Some specific checks.
        switch ($resourceName) {
            case 'items':
                // Currently, an item without files is unprocessable.
                $medias = $resource->media();
                if (!count($medias)) {
                    // return $view->translate('This item has no files and is not displayable.');
                    return '';
                }
                // Display the viewer only when at least one media is an image.
                $hasImage = false;
                foreach ($medias as $media) {
                    if ($media->ingester() === 'iiif' || strtok($media->mediaType(), '/') === 'image') {
                        $hasImage = true;
                        break;
                    }
                }
                if (!$hasImage) {
                    return '';
                }
                break;
            case 'item_sets':
                if ($resource->itemCount() == 0) {
                    // return $view->translate('This collection has no item and is not displayable.');
                    return '';
                }
                break;
        }

        $urlManifest = $view->iiifUrl($resource);
        return $this->render($urlManifest, $options, $resourceName);
    }

    /**
     * Render a mirador viewer for a url, according to options.
     *
     * @param string $urlManifest
     * @param array $options
     * @param string $resourceName
     * @param bool $isExternal If the manifest is managed by Omeka or not.
     * @return string Html code.
     */
    protected function render($urlManifest, array $options = [], $resourceName = null, $isExternal = false)
    {
        static $id = 0;

        $view = $this->view;

        $isSite = $view->params()->fromRoute('__SITE__');
        $setting = $isSite ? $view->plugin('siteSetting') : $view->plugin('setting');
        $this->version = $setting('mirador_version', '3');
        if ($this->version === '2') {
            return $this->renderMirador2($urlManifest, $options, $resourceName, $isExternal);
        }

        $assetUrl = $view->plugin('assetUrl');
        // No css in Mirador 3.
        $view->headScript()
            ->appendFile($assetUrl('vendor/mirador/mirador.min.js', 'Mirador'), 'text/javascript', ['defer' => 'defer'])
            ->appendFile($assetUrl('js/mirador.js', 'Mirador'), 'text/javascript', ['defer' => 'defer']);

        $view->partial('common/helper/mirador-plugins', [
            'plugins' => $setting('mirador_plugins', []),
        ]);

        $view->headLink()
            ->appendStylesheet($assetUrl('css/mirador.css', 'Mirador'));

        $config = [
            'id' => 'mirador-' . ++$id,
        ];

        $config['language'] = $view->identity()
            ? str_replace('_', '-', $view->userSetting('locale'))
            : str_replace('_', '-', $setting('locale'));

        $isCollection = false;
        $data = [];
        $location = '';
        if ($isExternal) {
            $site = $view->site;
            $location = $site ? $site->title() : '';
        }
        switch ($resourceName) {
            case 'items':
                $data = [
                    'windows' => [[
                        'manifestId' => $urlManifest,
                    ]],
                ];
                $data = $this->appendConfigData($data);
                $config += $data;
                $siteConfig = $setting('mirador_config_item', '{}') ?: '{}';
                // TODO Site settings are not checked in page site settings.
                if (json_decode($siteConfig, true) === null) {
                    $view->logger()->err('Settings for Mirador config of items is not a valid json.'); // @translate
                }
                break;
            case 'item_sets':
            case 'multiple':
                $isCollection = true;
                $data = [
                    'windows' => [[
                        'manifestId' => $urlManifest,
                    ]],
                ];
                if ($resourceName === 'item_sets') {
                    $data = $this->appendConfigData($data);
                }
                $config += $data;
                $siteConfig = $setting('mirador_config_collection', '{}') ?: '{}';
                if (json_decode($siteConfig, true) === null) {
                    $view->logger()->err('Settings for Mirador config of collections is not a valid json.'); // @translate
                }
                break;
        }

        /*
        $placeholders = [
            '__manifestUri__' => json_encode($urlManifest),
            '__canvasID__' => json_encode(
                $isCollection ? null : (substr($urlManifest, 0, -8) . 'canvas/p1')
            ),
        ];
        $siteConfig = str_replace(array_keys($placeholders), array_values($placeholders), $siteConfig);
        */
        $siteConfig = json_decode($siteConfig, true) ?: [];

        // Since only id, buildPath and data are set, it is possible to use
        // array_merge, array_replace or "+" operator.
        // In javascript, use "jQuery.extend(true, config, siteOptions, options)".
        $config = array_replace_recursive($config, $siteConfig, $options);

        return $view->partial('common/helper/mirador', [
            'config' => $config,
        ]);
    }

    /**
     * Render a mirador viewer for a url, according to options.
     *
     * @param string $urlManifest
     * @param array $options
     * @param string $resourceName
     * @param bool $isExternal If the manifest is managed by Omeka or not.
     * @return string Html code.
     */
    protected function renderMirador2($urlManifest, array $options = [], $resourceName = null, $isExternal = false)
    {
        static $id = 0;

        $view = $this->view;
        $isSite = $view->params()->fromRoute('__SITE__');
        $setting = $isSite ? $view->plugin('siteSetting') : $view->plugin('setting');
        $assetUrl = $view->plugin('assetUrl');

        $view->headLink()
            ->appendStylesheet($assetUrl('vendor/mirador-2/css/mirador-combined.min.css', 'Mirador'));
        $view->headScript()
            ->appendFile($assetUrl('vendor/mirador-2/mirador.min.js', 'Mirador'), 'text/javascript', ['defer' => 'defer'])
            ->appendFile($assetUrl('js/mirador-2.js', 'Mirador'), 'text/javascript', ['defer' => 'defer']);

        $view->partial('common/helper/mirador-2-plugins', [
            'plugins' => $setting('mirador_plugins', []),
        ]);

        $view->headLink()
            ->appendStylesheet($assetUrl('css/mirador.css', 'Mirador'));

        $config = [
            'id' => 'mirador-' . ++$id,
            'buildPath' => $assetUrl('vendor/mirador-2/', 'Mirador', false, false),
        ];

        // TODO Manage locale in Mirador.
        $config['locale'] = $view->identity()
            ? $view->userSetting('locale')
            : $setting('locale');
        $config['locale'] = substr($config['locale'], 0, 2);

        $isCollection = false;
        $data = [];
        $location = '';
        if ($isExternal) {
            $site = $view->site;
            $location = $site ? $site->title() : '';
        }
        switch ($resourceName) {
            case 'items':
                $data = [[
                    'manifestUri' => $urlManifest,
                    'location' => $location,
                ]];
                $data = $this->appendConfigData($data);
                $config += [
                    'data' => $data,
                    'windowObjects' => [['loadedManifest' => $urlManifest]],
                ];
                $siteConfig = $setting('mirador_config_item', '{}') ?: '{}';
                // TODO Site settings are not checked in page site settings.
                if (json_decode($siteConfig, true) === null) {
                    $view->logger()->err('Settings for Mirador config of items is not a valid json.'); // @translate
                }
                break;
            case 'item_sets':
            case 'multiple':
                $isCollection = true;
                $data = [[
                    'collectionUri' => $urlManifest,
                    'location' => $location,
                ]];
                if ($resourceName === 'item_sets') {
                    $data = $this->appendConfigData($data);
                }
                $config += [
                    'data' => $data,
                    'openManifestsPage' => true,
                ];
                $siteConfig = $setting('mirador_config_collection', '{}') ?: '{}';
                if (json_decode($siteConfig, true) === null) {
                    $view->logger()->err('Settings for Mirador config of collections is not a valid json.'); // @translate
                }
                break;
        }

        $placeholders = [
            '__manifestUri__' => json_encode($urlManifest),
            '__canvasID__' => json_encode(
                $isCollection ? null : (substr($urlManifest, 0, -8) . 'canvas/p1')
            ),
        ];
        $siteConfig = str_replace(array_keys($placeholders), array_values($placeholders), $siteConfig);
        $siteConfig = json_decode($siteConfig, true) ?: [];

        // Since only id, buildPath and data are set, it is possible to use
        // array_merge, array_replace or "+" operator.
        // In javascript, use "jQuery.extend(true, config, siteOptions, options)".
        $config = array_replace_recursive($config, $siteConfig, $options);

        return $view->partial('common/helper/mirador', [
            'config' => $config,
        ]);
    }

    protected function appendConfigData(array $data)
    {
        $view = $this->view;

        $isSite = $view->params()->fromRoute('__SITE__');
        $setting = $isSite ? $view->plugin('siteSetting') : $view->plugin('setting');

        $number = $setting('mirador_preselected_items');
        if (empty($number)) {
            return $data;
        }

        switch ($this->resource->resourceName()) {
            case 'items':
                $itemSets = $this->resource->itemSets();
                if (!count($itemSets)) {
                    return $data;
                }
                $collection = reset($itemSets);
                break;
            case 'item_sets':
                $collection = $this->resource;
                break;
        }

        $site = $view->vars('site');
        $location = $site ? $site->title() : '';

        // Allows to get the url quickly.
        $baseManifest = $view->url($this->isOldIiifServer ? 'iiifserver_presentation_item_redirect' : 'iiifserver/manifest-id', ['id' => '0'], ['force_canonical' => true]);
        $baseManifest = rtrim($baseManifest, '0');

        // The view api doesn't support "returnScalar", so use the api manager.
        $api = $this->resource->getServiceLocator()->get('Omeka\ApiManager');
        $ids = $api->search('items', ['item_set_id' => $collection->id(), 'limit' => $number, 'sort_by' => 'dcterms:title', 'sort_order' => 'asc'], ['returnScalar' => 'id'])->getContent();
        if ($this->version === '2') {
            foreach ($ids as $id) {
                $data[] = [
                    'manifestUri' => $baseManifest . $id . '/manifest',
                    'location' => $location,
                ];
            }
        } else {
            foreach ($ids as $id) {
                $data['manifests'][] = $baseManifest . $id . '/manifest';
            }
        }

        return $data;
    }

    /**
     * Get an asset path for a site from theme or module (fallback).
     *
     * @param string $path
     * @param string $module
     * @return string|null
     */
    protected function assetPath($path, $module = null)
    {
        // Check the path in the theme.
        if ($this->currentTheme) {
            $filepath = OMEKA_PATH . '/themes/' . $this->currentTheme->getId() . '/asset/' . $path;
            if (file_exists($filepath)) {
                return $this->view->assetUrl($path, null, false, false);
            }
        }

        // As fallback, get the path in the module (the file must exist).
        if ($module) {
            $assetPath = $this->view->assetUrl($path, $module, false, false);
            return $assetPath;
        }
    }
}
