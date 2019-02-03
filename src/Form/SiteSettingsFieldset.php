<?php
namespace Mirador\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    /**
     * @var bool
     */
    protected $iiifServerIsActive;

    public function init()
    {
        // The module iiif server is required to display collections of items.
        $iiifServerIsActive = $this->getIiifServerIsActive();

        $this->setLabel('Mirador Viewer'); // @translate

        $this->add([
            'name' => 'mirador_append_item_set_show',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item set page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'mirador_append_item_set_show',
            ],
        ]);

        $this->add([
            'name' => 'mirador_append_item_show',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'mirador_append_item_show',
            ],
        ]);

        $this->add([
            'name' => 'mirador_append_item_set_browse',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item sets browse page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'mirador_append_item_set_browse',
                'disabled' => !$iiifServerIsActive,
            ],
        ]);

        $this->add([
            'name' => 'mirador_append_item_browse',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Append automatically to item browse page', // @translate
                'info' => 'If unchecked, the viewer can be added via the helper in the theme or the block in any page.', // @translate
            ],
            'attributes' => [
                'id' => 'mirador_append_item_browse',
                'disabled' => !$iiifServerIsActive,
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
