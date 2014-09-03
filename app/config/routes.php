<?php
    namespace Thin;

    $route = route()
        ->setName('home')
        ->setPath('/')
        ->setModule('www')
        ->setController('static')
        ->setAction('home')
        ->assign();

    $route = route()
        ->setName('test')
        ->setPath('/(.*)/(.*)/test')
        ->setModule('www')
        ->setController('static')
        ->setAction('test')
        ->setParam1('variable_test')
        ->setSettings1(function ($var) use ($route) {
            return Inflector::upper($var);
        })
        ->setParam2('super')
        ->setSettings2(function ($var) use ($route) {
            return Inflector::upper($var);
        })
        ->assign();

    /* Backend */
    $route = route()
        ->setName('back_home')
        ->setPath('/backend')
        ->setModule('backend')
        ->setController('static')
        ->setAction('home')
        ->assign();

