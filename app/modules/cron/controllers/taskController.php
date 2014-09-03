<?php
    namespace Thin;
    class taskController extends Controller
    {
        public function init()
        {
            $this->action = container()->getRoute()->getAction();
        }

        public function preDispatch()
        {

        }

        public function testAction()
        {
            vd('ici');
        }

        public function postDispatch()
        {

        }
    }
