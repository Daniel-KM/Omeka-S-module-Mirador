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
                'info' => 'The property supplying the manifest URL for the viewer, for example "dcterms:hasFormat".', // @translate
                'empty_option' => 'Select a property…', // @translate
                'term_as_value' => true,
            ],
            'attributes' => [
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
