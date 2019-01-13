<?php

namespace Mirador\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\Theme\Theme;
use Zend\View\Helper\AbstractHelper;

class Mirador extends AbstractHelper
{
    /**
     * These options are used only when the player is called outside of a site
     * or when the site settings are not set. They can be bypassed by options
     * passed to the helper.
     *
     * @var array
     */
    protected $defaultOptions = [
        'class' => '',
        'style' => 'display: block; width: 90%; height: 600px; margin: 1em 5%; position: relative;',
        'locale' => 'en',
    ];

    /**
     * @var Theme The current theme, if any
     */
    protected $currentTheme;

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
     * @param AbstractResourceEntityRepresentation|array $resource
     * @param array $options Associative array of optional values:
     *   - (string) class
     *   - (string) locale
     *   - (string) style
     *   - (string) config
     * @return string. The html string corresponding to the Mirador.
     */
    public function __invoke($resource, $options = [])
    {
        if (empty($resource)) {
            return '';
        }

        $view = $this->getView();

        // If the manifest is not provided in metadata, point to the manifest
        // created from Omeka files only when the Iiif Server is installed.
        $iiifServerIsActive = $view->getHelperPluginManager()->has('iiifManifest');

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
            return $this->render($urlManifest, $options);
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
                return $this->render($urlManifest, $options);
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

        return $this->render($urlManifest, $resourceName, $options);
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
     * @return string
     */
    protected function render($urlManifest, $resourceName, $options = [])
    {
        $view = $this->view;

        // Check site, because site settings aren’t available outside of a site.
        $isSite = $view->params()->fromRoute('__SITE__');
        if (empty($isSite)) {
            $options += $this->defaultOptions;
        }

        $class = isset($options['class'])
            ? $options['class']
            : $view->siteSetting('mirador_class', $this->defaultOptions['class']);
        if (!empty($class)) {
            $class = ' ' . $class;
        }

        $style = isset($options['style'])
            ? $options['style']
            : $view->siteSetting('mirador_style', $this->defaultOptions['style']);

        $locale = isset($options['locale'])
            ? $options['locale']
            : $view->siteSetting('mirador_locale', $this->defaultOptions['locale']);

        $view->headLink()
            ->appendStylesheet($view->assetUrl('vendor/mirador/css/mirador-combined.min.css', 'Mirador'))
            ->appendStylesheet($view->assetUrl('css/mirador.css', 'Mirador'));
        $view->headScript()->appendFile($view->assetUrl('vendor/mirador/mirador.min.js', 'Mirador'));

        $config = [
            'id' => "mirador",
            'buildPath' => $view->assetUrl('vendor/mirador/', 'Mirador', false, false),
            'language' => $locale,
        ];

        switch($resourceName) {
            case 'items' :
                $config += [
                    'data' => [[
                        'manifestUri' => $urlManifest,
                        //'location' => "My Repository",
                    ]],
                    'windowObjects' => [['loadedManifest' => $urlManifest]]
                ];
                break;
            case 'item_sets' :
                $config += [
                    'data' => [['collectionUri' => $urlManifest]],
                    'openManifestsPage' => true,
                ];
                break;
        }

        return $view->partial('common/helper/mirador', [
            'class' => $class,
            'style' => $style,
            'config' => $config,
        ]);
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
