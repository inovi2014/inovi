<?php
    namespace Thin;
    use \Thin\CmsConfig as conf;

    return array(
        'product' => array(
            'path' => '/product/(.*)-(.*)',
            'closure' => function ($vars) {
                if (count($vars) == 2) {
                    list($slug, $id) = $vars;
                    $_REQUEST['slug'] = $slug;
                    $_REQUEST['id'] = $id;
                    return '/product/item.html';
                }
                return false;
            }
        ),
    );
