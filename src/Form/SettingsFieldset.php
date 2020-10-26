<?php declare(strict_types=1);

namespace Mirador\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\PropertySelect;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Mirador Viewer'; // @translate

    public function init(): void
    {
        $this
            ->add([
                'name' => 'mirador_manifest_property',
                'type' => PropertySelect::class,
                'options' => [
                    'label' => 'Manifest property', // @translate
                    'info' => 'The property supplying the manifest URL for the viewer, for example "dcterms:hasFormat" or "dcterms:isFormatOf".', // @translate
                    'empty_option' => '',
                    'term_as_value' => true,
                    'use_hidden_element' => true,
                ],
                'attributes' => [
                    'id' => 'mirador_manifest_property',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select a property…', // @translate
                ],
            ]);
    }
}
