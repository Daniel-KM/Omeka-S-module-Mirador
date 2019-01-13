<?php
namespace MiradorViewer\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    /** @var bool */
    public $iiifServerIsActive;

    public function init()
    {
        // The module iiif server is required to display collections of items.
        $iiifServerIsActive = $this->getIiifServerIsActive();

        $this->setLabel('Universal Viewer'); // @translate

        $this->add([
            'name' => 'miradorviewer_append_item_set_show',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item set page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'miradorviewer_append_item_set_show',
            ],
        ]);

        $this->add([
            'name' => 'miradorviewer_append_item_show',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'miradorviewer_append_item_show',
            ],
        ]);

        $this->add([
            'name' => 'miradorviewer_append_item_set_browse',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item sets browse page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'miradorviewer_append_item_set_browse',
                'disabled' => !$iiifServerIsActive,
            ],
        ]);

        $this->add([
            'name' => 'miradorviewer_append_item_browse',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item browse page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'miradorviewer_append_item_browse',
                'disabled' => !$iiifServerIsActive,
            ],
        ]);

        $this->add([
            'name' => 'miradorviewer_class',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Class of main div', // @translate
                'info' => 'Class to add to the main div.',  // @translate
            ],
            'attributes' => [
                'id' => 'miradorviewer_class',
            ],
        ]);

        $this->add([
            'name' => 'miradorviewer_style',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Inline style', // @translate
                'info' => 'If any, this style will be added to the main div of the Universal Viewer.' // @translate
                . ' ' . 'The height may be required.', // @translate
            ],
            'attributes' => [
                'id' => 'miradorviewer_style',
            ],
        ]);

        $this->add([
            'name' => 'miradorviewer_locale',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Locales of the viewer', // @translate
                'info' => 'Currently not working', // @translate
            ],
            'attributes' => [
                'id' => 'miradorviewer_locale',
            ],
        ]);
    }

    /**
     * @param bool $iiifServerIsActive
     */
    public function setIiifServerIsActive($iiifServerIsActive)
    {
        $this->iiifServerIsActive = $iiifServerIsActive;
    }

    /**
     * @return bool
     */
    public function getIiifServerIsActive()
    {
        return $this->iiifServerIsActive;
    }
}
