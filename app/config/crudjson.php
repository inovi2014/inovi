<?php
    namespace Thin;

    $tablesModels   = glob(APPLICATION_PATH . DS . 'models' . DS . 'CrudJson' . DS . '*.php');
    $tables         = array();

    if (count($tablesModels)) {
        foreach ($tablesModels as $tm) {
            $tab = explode(DS, $tm);
            $table = lcfirst(Inflector::uncamelize(repl('.php', '', Arrays::last($tab))));
            $tables[$table] = include($tm);
        }
    }

    return array('tables' => $tables);

