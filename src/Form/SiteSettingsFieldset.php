<?php declare(strict_types=1);

namespace Mirador\Form;

use Laminas\Form\Element;

class SiteSettingsFieldset extends \Mirador\Form\SettingsFieldset
{
    public function init(): void
    {
        parent::init();

        $this
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
