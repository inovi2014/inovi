<?php
    namespace Thin;
    use \CrudBundle\Crud as c;

    return array(
        /* GENERAL SETTINGS */
        'singular'                  => 'Utilisateur',
        'plural'                    => 'Utilisateurs',
        'default_order'             => 'name',
        'default_order_direction'   => 'ASC',
        'items_by_page'             => 25,
        'display'                   => 'firstname,name',
        'many'                      => array(),

        /* EVENTS */
        'before_create'             => function () {
            $_POST['password'] = sha1($_POST['password']);
        },
        'after_create'              => null,
        'before_read'               => null,
        'after_read'                => null,
        'before_update'             => function () {
            $_POST['password'] = !Inflector::isSha1($_POST['password']) ? sha1($_POST['password']) : $_POST['password'];
        },
        'after_update'              => null,
        'before_delete'             => null,
        'after_delete'              => null,

        /* FIELDS */
        'fields'                    => array(
            'username' => array(
                'label' => 'Identifiant',
            ),
            'name' => array(
                'label' => 'Nom',
            ),
            'firstname' => array(
                'label' => 'Prénom',
            ),
            'password' => array(
                'label' => 'Mot de passe',
                'is_listable' => false,
                'is_exportable' => false,
                'is_searchable' => false,
                'is_sortable' => false,
                'form_type' => 'password',
            ),
            'skype' => array(
                'is_listable' => false,
                // 'form_type' => 'time',
                'required' => false,
            ),
            'partner_id' => array(
                'label' => 'Partenaire',
                'content_view' => function ($row) {
                    return c::row($row, 'partner', 'name');
                },
                'content_list' => function ($row) {
                    return c::row($row, 'partner', 'name', false);
                },
                'content_search' => function ($idField) {
                    return c::rows($idField, 'partner', 'name', 'name');
                },
                'content_create' => function ($row, $required) {
                    return c::rowsForm('partner_id', 'partner', 'name', 'name', $row->partner_id, $required);
                }
            ),
            'gender' => array(
                'is_listable' => false,
                'label' => 'Civilité',
                'content_view' => function ($row) {
                    return c::vocabulary($row['gender'], 'Monsieur, Madame, Mademoiselle');
                },
                'content_list' => function ($row) {
                    return c::vocabulary($row['gender'], 'Monsieur, Madame, Mademoiselle');
                },
                'content_search' => function ($idField) {
                    return c::vocabularies($idField, 'Monsieur, Madame, Mademoiselle');
                },
                'content_create' => function ($row, $required) {
                    return c::vocabulariesForm('gender', 'Monsieur, Madame, Mademoiselle', $row->gender, $required);
                }
            ),
            'tel_work' => array(
                'label' => 'Tel',
            ),
            'tel_home' => array(
                'label' => 'Tel Dom.',
                'is_listable' => false,
            ),
            'tel_cellular' => array(
                'label' => 'Tel Mob.',
                'is_listable' => false,
            ),
            'twitter' => array(
                'is_listable' => false,
            ),
            'linkedin' => array(
                'is_listable' => false,
            ),
        )
    );
