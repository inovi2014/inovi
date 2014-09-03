<?php
    namespace Thin;
    class staticController extends Controller
    {
        public function init()
        {
            if (isset($this->view)) {
                $this->_url = $this->view->_url = context('url');
            }
        }

        public function preDispatch()
        {

        }

        public function indexAction()
        {

        }

        public function homeAction()
        {
            $this->view->title = 'Site';
        }

        public function page404Action()
        {
            $this->view->title = 'Not found';
        }

        public function postDispatch()
        {

        }
    }
