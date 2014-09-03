<?php
    namespace Thin;

    $bundles = array(
        // 'mongo',
        // 'eav',
        'dbjson',
        'auth',
        'crud',
        // 'mongodm',
        // 'eloquent',
        'cms', /* last place important because all other bundles are ever loaded */
    );

    foreach ($bundles as $bundle) {
        bundle($bundle);
    }

    class_alias('\\Dbjson\\DbJson', '\\Thin\\DBJ');
