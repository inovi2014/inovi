<?php
    namespace Thin;
    use \CrudBundle\Crud as c;

    return array(
        /* GENERAL SETTINGS */
        'singular'                  => 'RDV',
        'plural'                    => 'RDV',
        'default_order'             => 'appointment_id',
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
            'appointmentstatus_id' => array(
                'label' => 'Statut',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => function ($row) {
                    return c::row($row, 'appointmentstatus', 'value');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'appointmentstatus', 'value', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'appointmentstatus', 'value', 'value');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('appointmentstatus_id', 'appointmentstatus', 'value', 'value', $row->appointmentstatus_id, $required);
                },
            ),
            'appointmentlocation_id' => array(
                'label' => 'Lieu',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => function ($row) {
                    return c::row($row, 'appointmentlocation', 'value');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'appointmentlocation', 'value', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'appointmentlocation', 'value', 'value');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('appointmentlocation_id', 'appointmentlocation', 'value', 'value', $row->appointmentlocation_id, $required);
                },
            ),
            'contact_id' => array(
                'label' => 'Contact',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => function ($row) {
                    return c::row($row, 'contact', 'firstname,name');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'contact', 'firstname,name', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'contact', 'firstname,name', 'name');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('contact_id', 'contact', 'firstname,name', 'name', $row->contact_id, $required);
                },
            ),
            'owner_id' => array(
                'label' => 'Propriétaire',
                'form_type' => 'text',

                'required' => true,
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
            'date_add' => array(
                'label' => 'DateAdd',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => false,
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
            'date_start' => array(
                'label' => 'Date du RDV',
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
            'hour_start' => array(
                'label' => 'HourStart',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => false,
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
            'duration' => array(
                'label' => 'Duration',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => false,
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
                'label' => 'Address',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => false,
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
                'label' => 'Zipcode',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => false,
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
            'user_id' => array(
                'label' => 'Utilisateur',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => true,
                'is_exportable' => true,
                'is_searchable' => true,
                'is_sortable' => true,
                'is_readable' => true,
                'is_creatable' => true,
                'is_updatable' => true,

                'content_view' => function ($row) {
                    return c::row($row, 'user', 'firstname,name');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'user', 'firstname,name', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'user', 'firstname,name', 'name');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('user_id', 'user', 'firstname,name', 'name', $row->user_id, $required);
                },
            ),
            'contactdata' => array(
                'label' => 'Contactdata',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => false,
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
            'appointmentdata' => array(
                'label' => 'Appointmentdata',
                'form_type' => 'text',

                'required' => true,
                'is_listable' => false,
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
