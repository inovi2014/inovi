<?php
    namespace Thin;
    use \CrudBundle\Crud as c;

    return array(
        /* GENERAL SETTINGS */
        'singular'                  => 'Parteneraire',
        'plural'                    => 'Parteneraires',
        'default_order'             => 'name',
        'default_order_direction'   => 'ASC',
        'items_by_page'             => 25,
        'display'                   => 'name',
        'many'                      => array('user'),

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
            'partnerfather_id' => array(
                'label' => 'Parent',
                'form_type' => 'text',

                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => function ($row) {
                    return c::row($row, 'partnerfather', 'name');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'partnerfather', 'name', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'partnerfather', 'name', 'name');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('partnerfather_id', 'partnerfather', 'name', 'name', $row->partnerfather_id, $required);
                },
            ),
            'name' => array(
                'label' => 'Nom',
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
            'owner_id' => array(
                'label' => 'Propriétaire',
                'form_type' => 'text',

                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => function ($row) {
                    return c::row($row, 'owner', 'firstname,name');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'owner', 'firstname,name', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'owner', 'firstname,name', 'name');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('owner_id', 'owner', 'firstname,name', 'name', $row->owner_id, $required);
                },
            ),
            'tel' => array(
                'label' => 'Tel',
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
            'fax' => array(
                'label' => 'Fax',
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
            'email' => array(
                'label' => 'Email',
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
            'address' => array(
                'label' => 'Adresse',
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
            'zipcode' => array(
                'label' => 'CP',
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
            'city' => array(
                'label' => 'Ville',
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
            'country_id' => array(
                'label' => 'Pays',
                'form_type' => 'text',

                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => function ($row) {
                    return c::row($row, 'country', 'fr');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'country', 'fr', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'country', 'fr', 'fr');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('country_id', 'country', 'fr', 'fr', $row->country_id, $required);
                },
            ),

        )
    );
