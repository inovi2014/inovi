<?php
    namespace Thin;
    use \CrudBundle\Crud as bundleCrud;

    return array(
        /* GENERAL SETTINGS */
        'singular'                  => 'Maritalstatus',
        'plural'                    => 'Maritalstatuss',
        'default_order'             => 'maritalstatus_id',
        'default_order_direction'   => 'ASC',
        'items_by_page'             => 25,
        'display'                   => false,
        'many'                      => array(),

        /* EVENTS */
        'before_create'             => false,
        'after_create'              => false,

        'before_read'               => false,
        'after_read'                => false,

        'before_update'             => false,
        'after_update'              => false,

        'before_delete'             => false,
        'after_delete'              => false,

        'before_list'               => false,
        'after_list'                => false,

        /* FIELDS */
        'fields'                    => array(
                        'label' => array(
                'label' => 'Label',
                'form_type' => 'text',
                'helper' => false,

                'required' => true,
                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => false,
                'content_list' => false,
                'content_search' => false,
                'content_create' => false,
            ),

        )
    );
