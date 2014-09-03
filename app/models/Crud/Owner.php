<?php
    namespace Thin;
    use \CrudBundle\Crud as c;

    return array(
        /* GENERAL SETTINGS */
        'singular'                  => 'Propriétaire',
        'plural'                    => 'Propriétaires',
        'default_order'             => 'name',
        'default_order_direction'   => 'ASC',
        'items_by_page'             => 25,
        'display'                   => null,
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
            'owner_id' => array(
                'label' => 'Owner',
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
            'partner_id' => array(
                'label' => 'Partner',
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
            'gender' => array(
                'label' => 'Gender',
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
            'username' => array(
                'label' => 'Username',
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
            'name' => array(
                'label' => 'Name',
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
            'firstname' => array(
                'label' => 'Firstname',
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
            'password' => array(
                'label' => 'Password',
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
            'tel_work' => array(
                'label' => 'Tel_work',
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
            'tel_home' => array(
                'label' => 'Tel_home',
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
            'tel_cellular' => array(
                'label' => 'Tel_cellular',
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
            'twitter' => array(
                'label' => 'Twitter',
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
            'linkedin' => array(
                'label' => 'Linkedin',
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
            'skype' => array(
                'label' => 'Skype',
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
