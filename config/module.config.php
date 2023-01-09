<?php declare(strict_types=1);

namespace Mirador;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'iiifManifestExternal' => View\Helper\IiifManifestExternal::class,
        ],
        'factories' => [
            'mirador' => Service\ViewHelper\MiradorFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\ConfigForm::class => Form\ConfigForm::class,
            Form\Element\OptionalSelect::class => Form\Element\OptionalSelect::class,
            Form\Element\OptionalUrl::class => Form\Element\OptionalUrl::class,
        ],
        'factories' => [
            Form\SettingsFieldset::class => Service\Form\SettingsFieldsetFactory::class,
            Form\SiteSettingsFieldset::class => Service\Form\SiteSettingsFieldsetFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'mirador' => Site\BlockLayout\Mirador::class,
        ],
    ],
    'resource_page_block_layouts' => [
        'invokables' => [
            'mirador' => Site\ResourcePageBlockLayout\Mirador::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Mirador\Controller\Player' => Controller\PlayerController::class,
        ],
        // The aliases simplify the routing, the url assembly and allows to support module Clean url.
        'aliases' => [
            'Mirador\Controller\Item' => Controller\PlayerController::class,
            'Mirador\Controller\ItemSet' => Controller\PlayerController::class,
            'Mirador\Controller\CleanUrlController' => Controller\PlayerController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    // This route allows to have a url compatible with Clean url.
                    'resource-id' => [
                        'may_terminate' => true,
                        'child_routes' => [
                            'mirador' => [
                                'type' => \Laminas\Router\Http\Literal::class,
                                'options' => [
                                    'route' => '/mirador',
                                    'constraints' => [
                                        'controller' => 'item|item-set',
                                        'action' => 'play',
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
                    // This route is the default url.
                    'resource-id-mirador' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/:controller/:id/mirador',
                            'constraints' => [
                                'controller' => 'item|item-set',
                                'action' => 'play',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Mirador\Controller',
                                'controller' => 'Player',
                                'action' => 'play',
                                'id' => '\d+',
                            ],
                        ],
                    ],
                ],
            ],
            // This route allows to have a top url without Clean url.
            // TODO Remove this route?
            'mirador_player' => [
                'type' => \Laminas\Router\Http\Segment::class,
                'options' => [
                    'route' => '/:controller/:id/mirador',
                    'constraints' => [
                        'controller' => 'item|item-set',
                        'id' => '\d+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Mirador\Controller',
                        // '__SITE__' => true,
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
            // By exception, the name of this property is the same than
            // module iiif server (and other iiif viewer).
            // This option allows to use this module independantly, but for
            // consistency, the same name is used.
            'iiifserver_manifest_external_property' => 'dcterms:hasFormat',
        ],
        'settings' => [
            'mirador_version' => '3',
            'mirador_plugins' => [],
            'mirador_config_item' => null,
            'mirador_config_collection' => null,
            'mirador_plugins_2' => [],
            'mirador_config_item_2' => null,
            'mirador_config_collection_2' => null,
            'mirador_annotation_endpoint' => '',
            'mirador_preselected_items' => 0,
        ],
        'site_settings' => [
            'mirador_version' => '3',
            'mirador_plugins' => [],
            'mirador_config_item' => null,
            'mirador_config_collection' => null,
            'mirador_plugins_2' => [],
            'mirador_config_item_2' => null,
            'mirador_config_collection_2' => null,
            'mirador_annotation_endpoint' => '',
            'mirador_preselected_items' => 0,
            'mirador_skip_default_css' => false,
        ],
    ],
];
