<?php declare(strict_types=1);

namespace Mirador\Form;

use Common\Form\Element as CommonElement;
use Laminas\Form\Form;

class ConfigForm extends Form
{
    public function init(): void
    {
        $this
            ->add([
                // By exception, the name of this property is the same than
                // module iiif server.
                'name' => 'iiifserver_manifest_external_property',
                'type' => CommonElement\OptionalPropertySelect::class,
                'options' => [
                    'label' => 'Property supplying an external manifest', // @translate
                    'info' => 'External or static manifests can be more customized and may be quicker to be loaded. Usually, the property is "dcterms:hasFormat" or "dcterms:isFormatOf".', // @translate
                    'empty_option' => '',
                    'term_as_value' => true,
                    'use_hidden_element' => true,
                ],
                'attributes' => [
                    'id' => 'iiifserver_manifest_external_property',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select a property…', // @translate
                ],
            ]);
    }
}
