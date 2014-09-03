<?php
    namespace Thin;
    use \CrudBundle\Crud as c;

    return array(
        /* GENERAL SETTINGS */
        'singular'                  => 'Lieu RDV',
        'plural'                    => 'Lieux RDV',
        'default_order'             => 'appointmentlocation_id',
        'default_order_direction'   => 'ASC',
        'items_by_page'             => 25,
        'display'                   => null,
        'many'                      => array(),

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
                        'label' => array(
                'label' => 'Label',
                'form_type' => 'text',

                'required' => true,
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
            'value' => array(
                'label' => 'Value',
                'form_type' => 'text',

                'required' => true,
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
