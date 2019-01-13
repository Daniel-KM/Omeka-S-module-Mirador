<?php
namespace MiradorViewer;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'miradorViewer' => Service\ViewHelper\MiradorViewerFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'miradorViewer' => Site\BlockLayout\MiradorViewer::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'MiradorViewer\Controller\Player' => Controller\PlayerController::class,
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
            'miradorviewer_player' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:resourcename/:id/play-mirador',
                    'constraints' => [
                        'resourcename' => 'item|item\-set',
                        'id' => '\d+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'MiradorViewer\Controller',
                        'controller' => 'Player',
                        'action' => 'play',
                    ],
                ],
            ],

            // If really needed, the next route may be uncommented to keep
            // compatibility with the old schemes used by the plugin for Omeka 2
            // before the version 2.4.2.
            // 'miradorviewer_player_classic' => [
            //     'type' => 'segment',
            //     'options' => [
            //         'route' => '/:resourcename/play/:id',
            //         'constraints' => [
            //             'resourcename' => 'item|items|item\-set|item_set|collection|item\-sets|item_sets|collections',
            //             'id' => '\d+',
            //         ],
            //         'defaults' => [
            //             '__NAMESPACE__' => 'MiradorViewer\Controller',
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
    'miradorviewer' => [
        'config' => [
            'miradorviewer_manifest_property' => '',
        ],
        'site_settings' => [
            'miradorviewer_append_item_set_show' => true,
            'miradorviewer_append_item_show' => true,
            'miradorviewer_append_item_set_browse' => false,
            'miradorviewer_append_item_browse' => false,
            'miradorviewer_class' => '',
            'miradorviewer_style' => 'background-color: #000; height: 600px;',
            'miradorviewer_locale' => 'en-GB:English (GB),fr:French',
        ],
    ],
];
