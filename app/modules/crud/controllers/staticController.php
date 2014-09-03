<?php
    namespace Thin;
    use \CrudBundle\Crud as c;

    class staticController extends Controller
    {
        private $isAdmin = false;
        public function init()
        {
            $this->view->config = context('config')->load('crud');

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
                    $this->forward('home');
                }
            }
        }

        public function loginAction()
        {
            $this->view->error = false;

            if ($this->isPost()) {
                $auth = auth()->login(
                    request()->getUsername(),
                    request()->getPassword()
                );

                if (false === $auth) {
                    $this->view->error = true;
                } else {
                    Utils::go(request()->getUrl());
                }
            }
            $this->view->title = 'Connexion';
        }

        public function logoutAction()
        {
            auth()->logout();
            Utils::go(URLSITE . 'crud/static/login');
        }

        public function lostAction()
        {

        }

        public function homeAction()
        {
            $this->view->title = 'Tableau de bord';
        }

        public function forbiddenAction()
        {
            $this->view->title = 'Interdiction';
        }

        public function listAction()
        {
            $table = request()->getTable();

            if (!is_null($table)) {
                $permission = $table . '_list';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    c::generate($table);
                    $crud               = new c(model($table));
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
                    $this->forward('forbidden');
                }

            } else {
                $this->forward('home');
            }
        }

        function createAction()
        {
            $table = request()->getTable();

            if (!is_null($table)) {
                $permission = $table . '_create';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(model($table));

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
                    $this->forward('forbidden');
                }
            } else {
                $this->forward('home');
            }
        }

        function duplicateAction()
        {
            $table = request()->getTable();
            $id = request()->getId();

            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_duplicate';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(model($table));

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
                    $this->view->row = model($table)->find($id);

                    if (empty($this->view->row)) {
                        $this->forward('home');
                    }
                } else {
                    $this->forward('forbidden');
                }
            } else {
                $this->forward('home');
            }
        }

        function readAction()
        {
            $table = request()->getTable();
            $id = request()->getId();

            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_read';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(model($table));
                    $infos      = $crud->config();
                    $plural     = isAke($infos, 'plural', 'items');
                    $singular   = isAke($infos, 'singular', 'item');

                    $this->view->title  = 'Afficher - ' . $singular;
                    $this->view->plural = $plural;
                    $this->view->singular = $singular;
                    $this->view->pk = $crud->pk();
                    $this->view->fields = $crud->fields();
                    $this->view->fieldsInfos = isAke($infos, 'fields');
                    $this->view->row = model($table)->find($id, false);

                    if (empty($this->view->row)) {
                        $this->forward('home');
                    }
                } else {
                    $this->forward('forbidden');
                }
            } else {
                $this->forward('home');
            }
        }

        function updateAction()
        {
            $table = request()->getTable();
            $id = request()->getId();

            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_update';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $crud       = new c(model($table));

                    if (true === $this->isPost()) {
                        $pk = model($table)->pk();
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
                    $this->view->row = model($table)->find($id);

                    if (empty($this->view->row)) {
                        $this->forward('home');
                    }
                } else {
                    $this->forward('forbidden');
                }
            } else {
                $this->forward('home');
            }
        }

        function deleteAction()
        {
            $table = request()->getTable();
            $id = request()->getId();

            if (!is_null($table) && !is_null($id)) {
                $permission = $table . '_delete';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $row = model($table)->find($id);

                    if (empty($row)) {
                        $this->forward('home');
                    }

                    $infos      = isAke($this->view->config['tables'], $table);
                    $closure    = isAke($infos, 'before_delete', false);

                    if (false !== $closure && is_callable($closure)) {
                        $closure();
                    }

                    $crud       = new c(model($table));
                    $status     = $crud->delete($id);
                    $closure    = isAke($infos, 'after_delete', false);

                    if (false !== $closure && is_callable($closure)) {
                        $closure();
                    }

                    if (true === $status) {
                        $this->forward('list');
                    } else {
                        $this->forward('error');
                    }
                } else {
                    $this->forward('forbidden');
                }
            } else {
                $this->forward('home');
            }
        }

        public function manyAction()
        {
            $table      = request()->getTable();
            $foreign    = request()->getForeign();
            $id         = request()->getId();

            if (!is_null($table) && !is_null($foreign) && !is_null($id)) {
                $_REQUEST['crud_where'] = "$foreign%%=%%'$id'##";
                $this->forward('list');
            } else {
                $this->forward('home');
            }
        }

        public function usersrightsAction()
        {
            $userDb = jdb(Config::get('bundle.auth.database', 'auth'), Config::get('bundle.auth.table.user', 'user'));

            $this->view->users = $userDb->fetch()->order('name')->exec();
            $this->view->permissions = array(
                'list',
                'read',
                'create',
                'duplicate',
                'update',
                'delete',
                'export'
            );

            $this->view->title = 'Gestion des droits par utilisateur';
        }

        public function itemsrightsAction()
        {
            $userDb = jdb(Config::get('bundle.auth.database', 'auth'), Config::get('bundle.auth.table.user', 'user'));

            $this->view->users = $userDb->fetch()->order('name')->exec();
            $this->view->permissions = array(
                'list',
                'read',
                'create',
                'duplicate',
                'update',
                'delete',
                'export'
            );

            $this->view->title = 'Gestion des droits par item';
        }

        public function permissionAction()
        {
            $from = $_SERVER['HTTP_REFERER'];
            $lastAction = Arrays::last(explode('/', $from));

            $permissions = array(
                'list',
                'read',
                'create',
                'duplicate',
                'update',
                'delete',
                'export'
            );
            $user   = request()->getUser();
            $action = request()->getAction();
            $table  = request()->getTable();
            $right  = request()->getRight();

            if (!is_null($user) && !is_null($action) && !is_null($table) && !is_null($right) && Arrays::in($right, $permissions)) {
                $auth = auth($user);
                $role = $auth->getRole();

                if (false !== $auth->user() && false === $auth->is('admin')) {
                    $permission = $table . '_' . $right;

                    if ($action == 'can') {
                        $actualAuth = $auth->can($permission);

                        if (false === $actualAuth) {
                            $right = $auth->addPermission(array('name' => $permission));
                            $auth->addRolePermission($role, $right);
                        }
                    } elseif ($action == 'cannot') {
                        $actualAuth = $auth->can($permission);

                        if (true === $actualAuth) {
                            $actualPerm = $auth->permission($permission);
                            $auth->removePermission($actualPerm);
                        }
                    }
                }
                Utils::go(URLSITE . 'crud/static/' . $lastAction . '#item' . $user);
            } else {
                $this->forward('home');
            }
        }

        public function allrightsitemAction()
        {
            $userDb = jdb(Config::get('bundle.auth.database', 'auth'), Config::get('bundle.auth.table.user', 'user'));
            $from = $_SERVER['HTTP_REFERER'];
            $lastAction = Arrays::last(explode('/', $from));

            $permissions = array(
                'list',
                'read',
                'create',
                'duplicate',
                'update',
                'delete',
                'export'
            );

            $table  = request()->getTable();
            $action = request()->getAction();
            $users  = $userDb->fetch()->order('name')->exec();

            if (!is_null($table) && !is_null($action) && count($users)) {
                foreach ($users as $user) {
                    $auth = auth($user['id']);
                    $role = $auth->getRole();
                    if (false !== $auth->user() && false === $auth->is('admin')) {
                        foreach ($permissions as $perm) {
                            $permission = $table . '_' . $perm;
                            $actualAuth = $auth->can($permission);

                            if ($action == 'can' && false === $actualAuth) {
                                $right = $auth->addPermission(array('name' => $permission));
                                $auth->addRolePermission($role, $right);
                            }

                            if ($action == 'cannot' && true === $actualAuth) {
                                $actualPerm = $auth->permission($permission);
                                $auth->removePermission($actualPerm);
                            }
                        }
                    }
                }
                Utils::go(URLSITE . 'crud/static/' . $lastAction);
            } else {
                $this->forward('home');
            }
        }

        public function allrightsAction()
        {
            $from = $_SERVER['HTTP_REFERER'];
            $lastAction = Arrays::last(explode('/', $from));

            $permissions = array(
                'list',
                'read',
                'create',
                'duplicate',
                'update',
                'delete',
                'export'
            );
            $user   = request()->getUser();
            $action = request()->getAction();

            $items = $this->view->items;

            if (!is_null($user) && !is_null($action)) {
                $auth = auth($user);
                $role = $auth->getRole();

                if (false !== $auth->user() && false === $auth->is('admin')) {
                    if ($action == 'can') {
                        foreach ($items as $item => $info) {
                            foreach ($permissions as $perm) {
                                $permission = $item . '_' . $perm;
                                $actualAuth = $auth->can($permission);

                                if (false === $actualAuth) {
                                    $right = $auth->addPermission(array('name' => $permission));
                                    $auth->addRolePermission($role, $right);
                                }
                            }
                        }
                    } elseif ($action == 'cannot') {
                        foreach ($items as $item => $info) {
                            foreach ($permissions as $perm) {
                                $permission = $item . '_' . $perm;
                                $actualAuth = $auth->can($permission);

                                if (true === $actualAuth) {
                                    $actualPerm = $auth->permission($permission);
                                    $auth->removePermission($actualPerm);
                                }
                            }
                        }
                    }
                }
                Utils::go(URLSITE . 'crud/static/' . $lastAction);
            } else {
                $this->forward('home');
            }
        }

        public function userlistAction()
        {
            $userDb = jdb(
                Config::get('bundle.auth.database', 'auth'),
                Config::get('bundle.auth.table.user', 'user')
            );

            $this->view->users = $userDb->fetch()->order('name')->exec();
        }

        public function adduserAction()
        {
            $this->view->title = 'Ajouter un utlisateur';

            if (auth()->is('admin')) {
                if (true === $this->isPost()) {
                    auth()->register($_POST);
                    $this->forward('userlist');
                }
            } else {
                $this->forward('home');
            }
        }

        public function edituserAction()
        {
            $userDb = jdb(
                Config::get('bundle.auth.database', 'auth'),
                Config::get('bundle.auth.table.user', 'user')
            );

            $id = request()->getId();
            $this->view->title = 'Mettre à jour un utlisateur';

            if (!is_null($id) && auth()->is('admin')) {
                $old = $userDb->find($id);
                $this->view->row = $old->assoc();

                if (true === $this->isPost()) {

                    $_POST['password'] = !Inflector::isSha1($_POST['password'])
                    ? sha1($_POST['password'])
                    : $_POST['password'];

                    $old->hydrate()->save();
                    $this->forward('userlist');
                }
            } else {
                $this->forward('home');
            }
        }

        public function makeadminAction()
        {
            $id = request()->getId();

            if (!is_null($id) && auth()->is('admin')) {
                auth($id)->makeAdmin();
                $this->forward('userlist');
            } else {
                $this->forward('home');
            }
        }

        public function deleteuserAction()
        {
            $id = request()->getId();

            if (!is_null($id) && auth()->is('admin')) {
                $userDb = jdb(
                    Config::get('bundle.auth.database', 'auth'),
                    Config::get('bundle.auth.table.user', 'user')
                );
                $auth = auth($id);
                $role = $auth->unregister($userDb->find($id));
                $this->forward('userlist');
            } else {
                $this->forward('home');
            }
        }

        public function doclistAction()
        {
            $db = jdb('auth', 'doc');
            $this->view->docs = $db->fetch()->order('updated_at', 'DESC')->exec();
            $this->view->title = 'Liste des documents';
        }

        public function docaddAction()
        {
            $this->view->title = 'Ajouter un document';

            if (true === $this->isPost()) {
                $db     = jdb('auth', 'doc');
                $url    = upload('url');
                $name   = isAke($_POST, 'name');
                $user   = auth()->user();
                $owner  = isAke($user->assoc(), 'id');
                $db->replace(array('name' => $name), array('url' => $url, 'owner' => $owner));
                $this->forward('doclist');
            }
        }

        public function doceditAction()
        {
            $id = request()->getId();

            if (!is_null($id)) {
                $db = jdb('auth', 'doc');
                $this->view->title = 'Mettre à jour un document';
                $old = $db->find($id);

                if ($old) {
                    $this->view->row = $old->assoc();

                    if (true === $this->isPost()) {
                        $url    = upload('url');
                        $url    = is_null($url) ? $old->url : $url;
                        $name   = isAke($_POST, 'name');

                        $user   = auth()->user();
                        $owner  = isAke($user->assoc(), 'id');
                        $old->setUrl($url)->setOwner($owner)->setName($name)->save();
                        $this->forward('doclist');
                    }
                } else {
                    $this->forward('home');
                }
            } else {
                $this->forward('home');
            }
        }

        public function docdeleteAction()
        {
            $id = request()->getId();

            if (!is_null($id)) {
                $db = jdb('auth', 'doc');
                $row = $db->find($id);

                if ($row) {
                    $file = $row->getUrl();
                    $file = repl(URLSITE, '', $file);
                    $file = realpath(APPLICATION_PATH . '/../public/' . $file);
                    File::delete($file);
                    $row->delete();
                }
                $this->forward('doclist');
            } else {
                $this->forward('home');
            }
        }

        public function pagelistAction()
        {
            $permission = 'cms';
            $auth = auth()->can($permission);
            if (true === $auth || true === $this->isAdmin) {
                $dirPages = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'pages');
                $dirPartials = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'partials');

                if (!strlen($dirPages) || !strlen($dirPartials)) {
                    $this->forward('home');
                }

                $pages      = File::readdir($dirPages);
                $partials   = File::readdir($dirPartials);

                $this->view->pages = array();

                $this->view->pages['page']      = $pages;
                $this->view->pages['partiel']   = $partials;
                $this->view->title = 'Liste des pages';
                $this->view->prefixPage = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'pages') . DS;
                $this->view->prefixPartial = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'partials') . DS;
            } else {
                $this->forward('home');
            }
        }

        public function addpageAction()
        {
            $permission = 'cms';
            $auth = auth()->can($permission);
            if (true === $auth || true === $this->isAdmin) {
                if ($this->isPost()) {
                    $path = request()->getPath();
                    $file = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'pages') . DS . $path;
                    $html       = request()->getHtml();
                    $config     = request()->getConfig();
                    $content    = "/*
$config
*/
$html";
                    File::delete($file);
                    File::put($file, $content);
                    $this->forward('pagelist');
                } else {
                    $this->view->title = 'Ajouter une page';
                }
            } else {
                $this->forward('pagelist');
            }
        }

        public function deletepageAction()
        {
            $id = request()->getId();

            if (!is_null($id)) {
                $permission = 'cms';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $dirPages = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'pages');
                    $dirPartials = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'partials');

                    if (!strlen($dirPages) || !strlen($dirPartials)) {
                        $this->forward('home');
                    }

                    $pages      = File::readdir($dirPages);
                    $partials   = File::readdir($dirPartials);

                    $pages = array_merge($pages, $partials);
                    $key = $id - 1;
                    $file = isset($pages[$key]) ? $pages[$key] : false;

                    if (false !== $file) {
                        File::delete($file);
                    }
                }

                $this->forward('pagelist');
            } else {
                $this->forward('pagelist');
            }
        }
        public function editpageAction()
        {
            $id = request()->getId();

            if (!is_null($id)) {
                $permission = 'cms';
                $auth = auth()->can($permission);

                if (true === $auth || true === $this->isAdmin) {
                    $dirPages = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'pages');
                    $dirPartials = realpath(APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'content' . DS . SITE_NAME . DS . 'partials');

                    if (!strlen($dirPages) || !strlen($dirPartials)) {
                        $this->forward('home');
                    }

                    $pages      = File::readdir($dirPages);
                    $partials   = File::readdir($dirPartials);

                    $pages = array_merge($pages, $partials);
                    $key = $id - 1;
                    $file = isset($pages[$key]) ? $pages[$key] : false;

                    if (false !== $file) {
                        if ($this->isPost()) {
                            $html = request()->getHtml();
                            $config = request()->getConfig();
                            $content = "/*
    $config
*/
$html";
                            File::delete($file);
                            File::put($file, $content);
                            $this->forward('pagelist');
                        } else {
                            $content = fgc($file);
                            $this->view->configPage = repl(array('/*', '*/'), '', $this->getConfigPage($content));
                            $this->view->htmlPage = $this->getHtml($content);
                            $this->view->title = 'Mettre à jour une page';
                        }
                    } else {
                        $this->forward('pagelist');
                    }
                } else {
                    $this->forward('pagelist');
                }
            } else {
                $this->forward('pagelist');
            }
        }

        private function getConfigPage($content)
        {
            $res = preg_match('#/\*.+?\*/#s', $content, $config);
            if ($res !== 0) {
                $config = array_shift($config);
                return str_replace(array('/*', '*/'), '', $config);
            }
            return null;
        }

        private function getHtml($content)
        {
            $content = preg_replace('#/\*.+?\*/#s', '', $content);
            return $content;
        }

        public function preDispatch()
        {

        }

        public function postDispatch()
        {

        }
    }
