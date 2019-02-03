<?php
namespace Mirador;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'mirador' => Service\ViewHelper\MiradorFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'mirador' => Site\BlockLayout\Mirador::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Mirador\Controller\Player' => Controller\PlayerController::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\ConfigForm::class => Form\ConfigForm::class,
        ],
        'factories' => [
            Form\SiteSettingsFieldset::class => Service\Form\SiteSettingsFieldsetFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'mirador_player' => [
                'type' => \Zend\Router\Http\Segment::class,
                'options' => [
                    'route' => '/:resourcename/:id/mirador',
                    'constraints' => [
                        'resourcename' => 'item|item\-set',
                        'id' => '\d+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Mirador\Controller',
                        'controller' => 'Player',
                        'action' => 'play',
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'mirador' => [
        'config' => [
            'mirador_manifest_property' => '',
        ],
        'site_settings' => [
            'mirador_append_item_set_show' => true,
            'mirador_append_item_show' => true,
            'mirador_append_item_set_browse' => false,
            'mirador_append_item_browse' => false,
        ],
    ],
];
