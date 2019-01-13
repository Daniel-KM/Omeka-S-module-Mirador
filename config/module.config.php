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
                    'route' => '/:resourcename/:id/play-mirador',
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

            // If really needed, the next route may be uncommented to keep
            // compatibility with the old schemes used by the plugin for Omeka 2
            // before the version 2.4.2.
            // 'mirador_player_classic' => [
            //     'type' => 'segment',
            //     'options' => [
            //         'route' => '/:resourcename/play/:id',
            //         'constraints' => [
            //             'resourcename' => 'item|items|item\-set|item_set|collection|item\-sets|item_sets|collections',
            //             'id' => '\d+',
            //         ],
            //         'defaults' => [
            //             '__NAMESPACE__' => 'Mirador\Controller',
            //             'controller' => 'Player',
            //             'action' => 'play',
            //         ],
            //     ],
            // ],
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
            'mirador_class' => '',
            'mirador_style' => 'display: block; width: 90%; height: 600px; margin: 1em 5%; position: relative;',
            'mirador_locale' => 'en',
        ],
    ],
];
