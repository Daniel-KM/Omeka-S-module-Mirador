<?php

/*
 * Copyright 2015-2018 Daniel Berthereau
 * Copyright 2016-2017 BibLibre
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software. You can use, modify and/or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software’s author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user’s attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software’s suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace MiradorViewer\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\Theme\Theme;
use Zend\View\Helper\AbstractHelper;

class MiradorViewer extends AbstractHelper
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
        'style' => 'background-color: #000; height: 600px',
        'locale' => 'en-GB:English (GB),fr:French',
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
     * Get the Universal Viewer for the provided resource.
     *
     * Proxies to {@link render()}.
     *
     * @param AbstractResourceEntityRepresentation|array $resource
     * @param array $options Associative array of optional values:
     *   - (string) class
     *   - (string) locale
     *   - (string) style
     *   - (string) config
     * @return string. The html string corresponding to the MiradorViewer.
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
        $manifestProperty = $view->setting('miradorviewer_manifest_property');
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

        return $this->render($urlManifest, $options);
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
    protected function render($urlManifest, $options = [])
    {
        $view = $this->view;

        // Check site, because site settings aren’t available outside of a site.
        $isSite = $view->params()->fromRoute('__SITE__');
        if (empty($isSite)) {
            $options += $this->defaultOptions;
        }

        $class = isset($options['class'])
            ? $options['class']
            : $view->siteSetting('miradorviewer_class', $this->defaultOptions['class']);
        if (!empty($class)) {
            $class = ' ' . $class;
        }

        $style = isset($options['style'])
            ? $options['style']
            : $view->siteSetting('miradorviewer_style', $this->defaultOptions['style']);
        if (!empty($style)) {
            $style = ' style="' . $style . '"';
        }

        $locale = isset($options['locale'])
            ? $options['locale']
            : $view->siteSetting('miradorviewer_locale', $this->defaultOptions['locale']);
        if (!empty($locale)) {
            $locale = ' data-locale="' . $locale . '"';
        }

        $view->headScript()->appendFile(
            $view->assetUrl('vendor/uv/lib/embed.js', 'MiradorViewer', false, false),
            'application/javascript',
            ['id' => 'embedUV']
        );
        //<link rel="stylesheet" type="text/css" href="build/mirador/css/mirador-combined.css">
        //<script src="build/mirador/mirador.js"></script>


//        $html = sprintf(
//            '<div class="uv%s" data-config="%s" data-uri="%s"%s%s></div>',
//            $class,
//            $config,
//            $urlManifest,
//            $locale,
//            $style
//        );
//        $view->headScript()->appendFile(
//            $view->assetUrl('vendor/uv/lib/embed.js', 'MiradorViewer', false, false),
//            'application/javascript',
//            ['id' => 'embedUV']
//        );
//        $view->headScript()->appendScript('/* wordpress fix */', 'application/javascript');

        $config = [
            'id' => "miradorviewer",
            'data' => [

            ],
        ];

        return $view->partial('common/helper/mirador-viewer', [
            'class' => $class,
            'config' => $config,
        ]);

        return $html;
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
