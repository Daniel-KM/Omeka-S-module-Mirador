<?php declare(strict_types=1);

namespace Mirador\Site\ResourcePageBlockLayout;

use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\ResourcePageBlockLayout\ResourcePageBlockLayoutInterface;

class Mirador implements ResourcePageBlockLayoutInterface
{
    public function getLabel() : string
    {
        return 'Mirador IIIF viewer'; // @translate
    }

    public function getCompatibleResourceNames() : array
    {
        return [
            'items',
            'media',
            'item_sets',
        ];
    }

    public function render(PhpRenderer $view, AbstractResourceEntityRepresentation $resource) : string
    {
        return $view->partial('common/resource-page-block-layout/mirador', [
            'resource' => $resource,
        ]);
    }
}
