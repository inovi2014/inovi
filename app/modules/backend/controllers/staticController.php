<?php
    namespace Thin;
    class staticController extends Controller
    {
        public function init()
        {
            $this->_url = $this->view->_url = context('url');
            $this->action = container()->getRoute()->getAction();
            $this->view->isLogged = false;
            $this->can();
        }

        public function preDispatch()
        {

        }

        public function homeAction()
        {
            $this->view->title = 'Backend';
        }

        public function logoutAction()
        {
            session('backend')->erase();
            $this->view->isLogged = false;
            $this->_url->redirect('login');
        }

        public function loginAction()
        {
            if (!is_null($this->user)) {
                $this->_url->redirect('home');
            }
            if (true === context()->isPost()) {
                $db = em('backend', 'user');

                $user = $db
                ->where('pseudo = ' . request()->getPseudo())
                ->where('password = ' . sha1(request()->getPassword()))
                ->first();

                if (!empty($user)) {
                    session('backend')->setUser($user);
                    $this->_url->redirect('home');
                }
            }
            $this->view->title = 'Backend Login';
        }

        public function passwordAction()
        {
            $this->view->title = 'Backend Recover password';
        }

        private function can()
        {
            $nonLoginPages = array('login', 'password');
            $this->user = session('backend')->getUser();
            if (is_null($this->user) && !Arrays::in($this->action, $nonLoginPages)) {
                $this->_url->redirect('login');
            } else {
                if (!is_null($this->user)) {
                    $this->view->isLogged = true;
                }
            }
        }

        public function postDispatch()
        {

        }
    }
