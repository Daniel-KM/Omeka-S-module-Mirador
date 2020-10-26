<?php declare(strict_types=1);
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
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
        ],
        'factories' => [
            Form\SiteSettingsFieldset::class => Service\Form\SiteSettingsFieldsetFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'resource-id-mirador' => [
                        'type' => \Laminas\Router\Http\Segment::class,
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
            'mirador_player' => [
                'type' => \Laminas\Router\Http\Segment::class,
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
        'settings' => [
            'mirador_manifest_property' => '',
        ],
        'site_settings' => [
            'mirador_version' => '2',
            'mirador_plugins' => [],
            'mirador_config_item' => null,
            'mirador_config_collection' => null,
            'mirador_preselected_items' => 0,
        ],
    ],
];
