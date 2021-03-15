<?php declare(strict_types=1);

namespace Mirador\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

/**
 * This is copy of the same viewer in module Iiif Server, so this module can be
 * used alone when urls are provided.
 */
class IiifManifestExternal extends AbstractHelper
{
    /**
     * Get the external manifest of a resource.
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource): ?string
    {
        $manifestProperty = $this->view->setting('iiifserver_manifest_external_property');
        // Manage the case where the url is saved as an uri or a text and the
        // case where the property contains other values that are not url.
        foreach ($resource->value($manifestProperty, ['all' => true]) as $urlManifest) {
            if ($urlManifest->type() === 'uri') {
                return $urlManifest->uri();
            }
            $urlManifest = (string) $urlManifest;
            if (filter_var($urlManifest, FILTER_VALIDATE_URL)) {
                return $urlManifest;
            }
        }
        return null;
    }
}
