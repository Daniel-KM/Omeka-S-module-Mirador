<?php
namespace Mirador\Form;

use Omeka\Form\Element\PropertySelect;
use Zend\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'mirador_manifest_property',
            'type' => PropertySelect::class,
            'options' => [
                'label' => 'Manifest property', // @translate
                'info' => 'The property supplying the manifest URL for the viewer, for example "dcterms:hasFormat" or "dcterms:isFormatOf".', // @translate
                'empty_option' => '',
                'term_as_value' => true,
            ],
            'attributes' => [
                'id' => 'mirador_manifest_property',
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a property…', // @translate
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'mirador_manifest_property',
            'required' => false,
        ]);
    }
}
