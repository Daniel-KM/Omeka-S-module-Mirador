<?php

namespace Mirador\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\Theme\Theme;
use Zend\View\Helper\AbstractHelper;

class Mirador extends AbstractHelper
{
    /**
     * @var Theme The current theme, if any
     */
    protected $currentTheme;

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
    public function __invoke($resource, $options = [])
    {
        if (empty($resource)) {
            return '';
        }
        $this->resource = $resource;

        $view = $this->getView();

        // If the manifest is not provided in metadata, point to the manifest
        // created from Omeka files only when the Iiif Server is installed.
        $iiifServerIsActive = $this->hasIiifServer();

        // Prepare the url of the manifest for a dynamic collection.
        if (is_array($resource)) {
            if (!$iiifServerIsActive) {
                return '';
            }

            $identifier = $this->buildIdentifierForList($resource);
            $route = 'iiifserver_presentation_collection_list';
            $urlManifest = $view->url(
                $route,
                ['id' => $identifier],
                ['force_canonical' => true]
            );
            $urlManifest = $view->iiifForceBaseUrlIfRequired($urlManifest);
            return $this->render($urlManifest, $options, 'multiple');
        }

        // Prepare the url for the manifest of a record after additional checks.
        $resourceName = $resource->resourceName();
        if (!in_array($resourceName, ['items', 'item_sets'])) {
            return '';
        }

        // Determine the url of the manifest from a field in the metadata.
        $urlManifest = '';
        $manifestProperty = $view->setting('mirador_manifest_property');
        if ($manifestProperty) {
            $urlManifest = $resource->value($manifestProperty);
            if ($urlManifest) {
                // Manage the case where the url is saved as an uri or a text.
                $urlManifest = $urlManifest->uri() ?: $urlManifest->value();
                return $this->render($urlManifest, $options, $resourceName, true);
            }
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
                if (count($resource->media()) == 0) {
                    // return $view->translate('This item has no files and is not displayable.');
                    return '';
                }
                $route = 'iiifserver_presentation_item';
                break;
            case 'item_sets':
                if ($resource->itemCount() == 0) {
                    // return $view->translate('This collection has no item and is not displayable.');
                    return '';
                }
                $route = 'iiifserver_presentation_collection';
                break;
        }

        $urlManifest = $view->url(
            $route,
            ['id' => $resource->id()],
            ['force_canonical' => true]
        );
        $urlManifest = $view->iiifForceBaseUrlIfRequired($urlManifest);

        return $this->render($urlManifest, $options, $resourceName);
    }

    /**
     * Helper to create an identifier from a list of records.
     *
     * The dynamic identifier is a flat list of ids: "5,1,2,3".
     * If there is only one id, a comma is added to avoid to have the same route
     * than the collection itself.
     * In all cases the order of records is kept.
     *
     * @todo Use IiifServer\View\Helper\IiifCollectionList::buildIdentifierForList()
     *
     * @param array $resources
     * @return string
     */
    protected function buildIdentifierForList($resources)
    {
        $identifiers = [];
        foreach ($resources as $resource) {
            $identifiers[] = $resource->id();
        }

        $identifier = implode(',', $identifiers);

        if (count($identifiers) == 1) {
            $identifier .= ',';
        }

        return $identifier;
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

        $view->headLink()
            ->appendStylesheet($view->assetUrl('vendor/mirador/css/mirador-combined.min.css', 'Mirador'));
        $view->headScript()
            ->appendFile($view->assetUrl('vendor/mirador/mirador.min.js', 'Mirador'));

        $view->partial('common/helper/mirador-plugins', [
            'plugins' => $view->siteSetting('mirador_plugins', []),
        ]);

        $view->headLink()
            ->appendStylesheet($view->assetUrl('css/mirador.css', 'Mirador'));

        $config = [
            'id' => 'mirador-' . ++$id,
            'buildPath' => $view->assetUrl('vendor/mirador/', 'Mirador', false, false),
        ];

        // TODO Manage locale in Mirador.
        $config['locale'] = $view->identity()
            ? $view->userSetting('locale')
            : ($view->params()->fromRoute('__SITE__')
                ? $view->siteSetting('locale')
                : $view->setting('locale'));

        $isCollection = false;
        $data = [];
        $location = '';
        if ($isExternal) {
            $site = $view->site();
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
                $siteConfig = $view->siteSetting('mirador_config_item', '{}');
                // TODO Site settings are not checked in page site settings.
                if (json_decode($siteConfig, true) === null) {
                    $view->logger()->err('Site settings for Mirador config of items is not a valid json.'); // @translate
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
                $siteConfig = $view->siteSetting('mirador_config_collection', '{}');
                if (json_decode($siteConfig, true) === null) {
                    $view->logger()->err('Site settings for Mirador config of collections is not a valid json.'); // @translate
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

        $number = $view->siteSetting('mirador_preselected_items');
        if (empty($number)) {
            return $data;
        }

        switch ($this->resource->resourceName()) {
            case 'items':
                $itemSets = $this->resource->itemSets();
                if (!$itemSets) {
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

        $baseManifest = $view->url('iiifserver_presentation_item_redirect', ['id' => '0']);
        $baseManifest = trim($baseManifest, '0');

        // The view api doesn't support "returnScalar", so use the api manager.
        $api = $this->resource->getServiceLocator()->get('Omeka\ApiManager');
        $ids = $api->search('items', ['item_set_id' => $collection->id(), 'limit' => $number, 'sort_by' => 'dcterms:title', 'sort_order' => 'asc'], ['returnScalar' => 'id'])->getContent();
        foreach ($ids as $id) {
            $data[] = [
                'manifestUri' => $baseManifest . $id . '/manifest',
                'location' => $location,
            ];
        }

        return $data;
    }

    protected function hasIiifServer()
    {
        return $this->getView()->getHelperPluginManager()->has('iiifManifest');
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
