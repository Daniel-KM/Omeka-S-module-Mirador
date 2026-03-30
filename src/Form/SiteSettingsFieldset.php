<?php declare(strict_types=1);

namespace Mirador\Form;

use Common\Form\Element as CommonElement;
use Laminas\Form\Element;

class SiteSettingsFieldset extends \Mirador\Form\SettingsFieldset
{
    protected $elementGroups = [
        'player' => 'Players', // @translate
        'themes_old' => 'Old themes', // @translate
    ];

    public function init(): void
    {
        parent::init();

        $this
            ->add([
                'name' => 'mirador_placement',
                'type' => CommonElement\OptionalMultiCheckbox::class,
                'options' => [
                    'element_group' => 'themes_old',
                    'label' => 'Display Mirador viewer (old themes)', // @translate
                    'value_options' => [
                        'after/items' => 'Item show', // @translate
                        'browse/items' => 'Item browse', // @translate
                        'browse/item_sets' => 'Item set browse', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'mirador_placement',
                    'required' => false,
                ],
            ])
            ->add([
                'name' => 'mirador_skip_default_css',
                'type' => Element\Checkbox::class,
                'options' => [
                    'element_group' => 'player',
                    'label' => 'Skip default css', // @translate
                ],
                'attributes' => [
                    'id' => 'mirador_skip_default_css',
                ],
            ])
        ;
    }
}
