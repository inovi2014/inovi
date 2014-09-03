<?php
    namespace Thin;
    use \CrudBundle\Crud as c;

    return array(
        /* GENERAL SETTINGS */
        'singular'                  => 'Pays',
        'plural'                    => 'Pays',
        'default_order'             => 'country_code',
        'default_order_direction'   => 'ASC',
        'items_by_page'             => 25,
        'display'                   => null,
        'many'                      => array('partner'),

        /* EVENTS */
        'before_create'             => null,
        'after_create'              => null,

        'before_read'               => null,
        'after_read'                => null,

        'before_update'             => null,
        'after_update'              => null,

        'before_delete'             => null,
        'after_delete'              => null,

        'before_list'               => null,
        'after_list'                => null,

        /* FIELDS */
        'fields'                    => array(
            'country_code' => array(
                'label' => 'Code',
                'form_type' => 'text',

                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => null,
                'content_list' => null,
                'content_search' => null,
                'content_create' => null,
            ),
            'fr' => array(
                'label' => 'Fr',
                'form_type' => 'text',

                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => null,
                'content_list' => null,
                'content_search' => null,
                'content_create' => null,
            ),
            'en' => array(
                'label' => 'En',
                'form_type' => 'text',

                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => null,
                'content_list' => null,
                'content_search' => null,
                'content_create' => null,
            ),
        )
    );
