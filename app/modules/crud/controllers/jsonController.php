<?php
    namespace Thin;
    use \Dbjson\Crud as c;

    class jsonController extends Controller
    {
        private $isAdmin = false;
        public function init()
        {
            $this->view->config = context('config')->load('crudjson');

            $guestActions = array(
                'login',
                'logout',
                'lost'
            );
            $guestActions += arrayGet($this->view->config, 'guest.actions', array());

            $this->view->isAuth = false;
            $this->view->items = isAke($this->view->config, 'tables');

            $user = auth()->user();
            if (!$user) {
                if (!Arrays::in($this->action(), $guestActions)) {
                    $this->forward('login');
                }
            } else {
                $this->view->isAuth = true;
                $this->view->user = $user;
                $this->isAdmin = auth()->is('admin');
                if ($this->action() == 'login' || $this->action() == 'lost') {
                    $this->forward('home', 'static');
                }
            }
        }

        public function listAction()
        {
            $table = request()->getTable();
            if (!is_null($table)) {
                $permission = $table . '_json_list';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    // c::generate($table);
                    $crud               = new c(jmodel($table));
                    $infos              = isAke($this->view->config['tables'], $table);
                    $closure            = isAke($infos, 'before_list', false);

                    if (false !== $closure && is_callable($closure)) {
                        $closure();
                    }

                    $plural             = isAke($infos, 'plural', 'items');
                    $singular           = isAke($infos, 'singular', 'item');
                    $this->view->title  = 'Liste des ' . $plural;
                    $this->view->plural = $plural;
                    $this->view->singular = $singular;
                    $fields = $crud->fields();
                    if (!count($fields) || count($fields) == 1) {
                        $this->forward('home', 'static');
                    }
                    c::generate($table);
                    $foreigns = array();

                    foreach ($fields as $field) {
                        if ($field != $crud->pk()) {
                            if (substr($field, -3) == '_id') {
                                array_push($foreigns, $field);
                            }
                        }
                    }

                    $this->view->list        = $crud->listing();
                    $this->view->search      = $crud->makeSearch();
                    $this->view->foreigns    = $foreigns;
                    $this->view->fieldsInfos = isAke($infos, 'fields');
                    $closure = isAke($infos, 'after_list', false);

                    if (false !== $closure && is_callable($closure)) {
                        $closure();
                    }
                } else {
                    $this->forward('forbidden', 'static');
                }

            } else {
                $this->forward('home', 'static');
            }
        }

        function createAction()
        {
            $table = request()->getTable();
            if (!is_null($table)) {
                $permission = $table . '_json_create';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(jmodel($table));

                    if (true === $this->isPost()) {
                        $status = $crud->form();
                        if (true === $status) {
                            $this->forward('list');
                        }
                    }

                    $infos      = $crud->config();
                    $singular   = isAke($infos, 'singular', 'item');

                    $this->view->title  = 'Créer - ' . $singular;
                    $this->view->singular = $singular;
                    $this->view->fields = $crud->fields();
                    $this->view->fieldsInfos = isAke($infos, 'fields');
                    $this->view->pk = $crud->pk();
                } else {
                    $this->forward('forbidden', 'static');
                }
            } else {
                $this->forward('home', 'static');
            }
        }

        function duplicateAction()
        {
            $table = request()->getTable();
            $id = request()->getId();
            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_json_duplicate';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(jmodel($table));

                    if (true === $this->isPost()) {
                        $_POST['duplicate_id'] = $id;
                        $status = $crud->form();
                        if (true === $status) {
                            $this->forward('list');
                        }
                    }

                    $infos      = $crud->config();
                    $singular   = isAke($infos, 'singular', 'item');

                    $this->view->title  = 'Dupliquer - ' . $singular;
                    $this->view->singular = $singular;
                    $this->view->fields = $crud->fields();
                    $this->view->fieldsInfos = isAke($infos, 'fields');
                    $this->view->pk = $crud->pk();
                    $this->view->row = jmodel($table)->find($id);
                    if (empty($this->view->row)) {
                        $this->forward('home', 'static');
                    }
                } else {
                    $this->forward('forbidden', 'static');
                }
            } else {
                $this->forward('home', 'static');
            }
        }

        function readAction()
        {
            $table  = request()->getTable();
            $id     = request()->getId();

            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_json_read';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(jmodel($table));
                    $infos      = $crud->config();
                    $plural     = isAke($infos, 'plural', 'items');
                    $singular   = isAke($infos, 'singular', 'item');

                    $this->view->title  = 'Afficher - ' . $singular;
                    $this->view->plural = $plural;
                    $this->view->singular = $singular;
                    $this->view->pk = $crud->pk();
                    $this->view->fields = $crud->fields();
                    $this->view->fieldsInfos = isAke($infos, 'fields');
                    $this->view->row = jmodel($table)->find($id, false);
                    if (empty($this->view->row)) {
                        $this->forward('home', 'static');
                    }
                } else {
                    $this->forward('forbidden', 'static');
                }
            } else {
                $this->forward('home', 'static');
            }
        }

        function updateAction()
        {
            $table  = request()->getTable();
            $id     = request()->getId();

            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_json_update';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(jmodel($table));

                    if (true === $this->isPost()) {
                        $pk = jmodel($table)->pk();
                        $_POST[$pk] = $id;
                        $status = $crud->form();
                        if (true === $status) {
                            $this->forward('list');
                        }
                    }

                    $infos      = $crud->config();
                    $singular   = isAke($infos, 'singular', 'item');

                    $this->view->title  = 'Mettre à jour - ' . $singular;
                    $this->view->singular = $singular;
                    $this->view->fields = $crud->fields();
                    $this->view->fieldsInfos = isAke($infos, 'fields');
                    $this->view->pk = $crud->pk();
                    $this->view->row = jmodel($table)->find($id);
                    if (empty($this->view->row)) {
                        $this->forward('home', 'static');
                    }
                } else {
                    $this->forward('forbidden', 'static');
                }
            } else {
                $this->forward('home', 'static');
            }
        }

        function deleteAction()
        {
            $table = request()->getTable();
            $id = request()->getId();

            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_json_delete';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $row = jmodel($table)->find($id);

                    if (empty($row)) {
                        $this->forward('home', 'static');
                    }

                    $infos      = isAke($this->view->config['tables'], $table);
                    $closure    = isAke($infos, 'before_delete', false);

                    if (false !== $closure && is_callable($closure)) {
                        $closure();
                    }

                    $crud       = new c(jmodel($table));
                    $status     = $crud->delete($id);
                    $closure    = isAke($infos, 'after_delete', false);

                    if (false !== $closure && is_callable($closure)) {
                        $closure();
                    }

                    if (true === $status) {
                        $this->forward('list');
                    } else {
                        $this->forward('error', 'static');
                    }
                } else {
                    $this->forward('forbidden', 'static');
                }
            } else {
                $this->forward('home', 'static');
            }
        }

        public function manyAction()
        {
            $table      = request()->getTable();
            $foreign    = request()->getForeign();
            $id         = request()->getId();

            if (!is_null($table) && !is_null($foreign) && !is_null($id)) {
                $_REQUEST['crud_where'] = "$foreign%%=%%$id##";
                $this->forward('list');
            } else {
                $this->forward('home', 'static');
            }
        }

        public function preDispatch()
        {

        }

        public function postDispatch()
        {

        }
    }
