<?php
    namespace Thin;

    class Bootstrap
    {
        public static $bag = array();

        public static function cli()
        {
            static::run(true);
        }

        public static function run($cli = false)
        {
            static::$bag['config'] = include __DIR__ . DS . 'config' . DS . 'application.php';
            require __DIR__ . DS . 'config' . DS . 'context.php';
            require __DIR__ . DS . 'config' . DS . 'routes.php';

            if (File::exists(__DIR__ . DS . 'config' . DS . 'bundles.php')) {
                require __DIR__ . DS . 'config' . DS . 'bundles.php';
            }

            if (false === $cli) {
                context()->router();
                static::tests();
                static::dispatch();
            }
        }

        private static function dispatch()
        {
            $route = Utils::get('appDispatch');

            if (!$route instanceof Container) {
                context()->is404();
                $route = container()->getRoute();
            }

            if (true !== container()->getIsDispatched()) {
                if (true !== $route->getCache()) {
                    context()->dispatch($route);
                } else {
                    $redis = context()->redis();
                    $key = sha1(serialize($route->assoc())) . '::routeCache';
                    $cached = $redis->get($key);
                    if (!strlen($cached)) {
                        ob_start();
                        context()->dispatch($route);
                        $cached = ob_get_contents();
                        ob_end_clean();
                        $redis->set($key, $cached);
                        $ttl = Config::get('application.route.cache', 7200);
                        $redis->expire($key, $ttl);
                    }
                    echo $cached;
                }
            }
        }

        private static function tests()
        {
            // dd(DBJ::optionFindById(1));
            // dd(DBO::instance());
            // throw new \InvalidArgumentException('cool');
            // $db = eav('core', 'test');
            // $max = 5000;
            // $start = 0;
            // while ($start < $max) {
            //     $start++;
            //     $row = $db->save(array(
            //         'name' => Utils::token(),
            //         'price' => rand(250, 1599)
            //     ));
            // }
            // $all = $db->all();
            // $prices = $db->where('price > 1000')->order('price', 'DESC')->first();
            // Timer::stop();
            // vd($prices);
            // dd(Timer::get());
            // $db = model('user');
            // dd($db->trick(
            //     function ($row) {
            //         return strstr($row['name'], 'z') ? true : false;
            //     }
            // )->trick(
            //     function ($row) {
            //         return strstr($row['name'], 'q') ? true : false;
            //     }
            // )->run());
            // $jdb = jdb('core', 'product');
            // $db = jmodel('option');
            // $row = $db->create()->setName('site.lng')->setValue('fr')->save();
            // dd($row);
            // $max = 5000;
            // $start = 0;
            // while ($start < $max) {
                // $jdb->create()->setName(Utils::token())->setPrice(rand(150, 15000))->save();
            //     $start++;
            // }
            // exit;
            // $res = $jdb->where('price < 1000')->first();
            // $jdb->find(15, false);
            // $res = $jdb->fetch()->exec();
            // Timer::stop();
            // dd($res);
            // dd(Timer::get());
            // dd(Arrays::first($res));
            // dd(app());
            // $m = new \Mongood\Mongo;
            // dd($m);
            // $response = Mandrill::send(array(
            //     'message' => array(
            //         'html' => 'Body of the message.',
            //         'subject' => 'Subject of the message.',
            //         'from_email' => 'monkey@somewhere.com',
            //         'to' => array(array('email' => 'gplusquellec@gmail.com')),
            //     ),
            // ));
            // dd($response);
            // context()->log('cool');
            // $jdb = jdb('core', 'product');
            // $p = $jdb->create()->setName('TV4')->setPrice(295)->save();
            // $p = $jdb->between('price', 100, 600, true);
            // $p = $jdb->fetch()->order('price')->select('price', true);
            // $p = $jdb->fetch()->order('price')->only('name');
            // $jdb::set('test2', time(), 30);
            // dd($jdb::get('test2'));
            // $db = model('truc');
            // dd($p);
            // $auth = auth();
            // $auth = auth(1);
            // dd($auth);

            // $role = $auth->addRole(array('name' => 'admin'));

            // $create = $auth->addPermission(array('name' => 'create'));
            // $read   = $auth->addPermission(array('name' => 'read'));
            // $update = $auth->addPermission(array('name' => 'update'));
            // $delete = $auth->addPermission(array('name' => 'delete'));

            // $auth->addRolePermission($role, $create);
            // $auth->addRolePermission($role, $read);
            // $auth->addRolePermission($role, $update);
            // $auth->addRolePermission($role, $delete);
            // $auth->addUserRole($role);
            // $auth->register(
            //     array(
            //         'username' => 'gplusquellec',
            //         'password' => '230266gp',
            //         'email' => 'gplusquellec@gmail.com',
            //         'name' => 'Plusquellec',
            //         'firstname' => 'Gérald',
            //     )
            // );
            // exit;
            // $auth->logout();
            // $auth->login('gplusquellec', '230266gp');
            // dd($auth->can('read'));
            // $db = eloquent('book');
            // $b = $db->find(1);
            // $b->print_me = 1;
            // dd($db::avg('id'));
            // $b = repo('Book');
            // $books = $b->findAll();
            // foreach ($books as $book) {
            //     echo sprintf("-%s\n", $book->getName());
            // }
            // dd(entity()->find(doctrine('Book'), 11));
            // exit;
            // $db = with(new Pdo)->getDriver(with(new Container)->setHost('localhost')->setUser('root')->setPassword('root')->setDbname('albumblog'));
            // $books = $db->fetchAll('SELECT * FROM book where id = 11453');
            // dd($db);
            // $db = em('core', 'test');
            // $record = $db->create(array('category_id' => 'relation'))->setName('truc')->setPrice(21)->linkCategory(em('core', 'category')->create()->setName('test')->save());
            // dd($record);
            // dd(container()->tbm('test', 'test')->backup());
            // events()->listen('mailer.receipt', function () {
            //     return rand(5, 15);
            // });
            // dd(events()->fire('mailer.receipt'));
            // event('mailer.receipt', function () {
            //     return rand(5, 15);
            // });
            // dd(fire('mailer.receipt'));
            // $f = iniLoad('test');
            // dd($f->toArray());
            // $s->setTruc('machin');
            // dd($product);
            // $db = model('book');
            // dd($db->find(11453)->id());
            // dd($db->find(11453)->linkOffer(db()->model('offer')->find(64))->offer(false));
            // $res = $db->sql()->join(db()->model('offer'))->where('offer_id > 0')->limit(30)->toJson();
            // dd($res);
            // $c = new Closure(function(){return true;});
            // dd(bundle('emailing', 'myBundle'));
            // bundle('emailing');
            // bundle('mongodm');
            // $db->extend('test', function () {
            //     return rand(144, 425);
            // });
            // $record = $db->find(4)->test();
            // dd(model('book'));
            // $c = collection();
            // $c->add(with(new Container)->setTime(time()));
            // dd($c);
            // $dql = "SELECT b FROM " . doctrine('Book') . " b";
            // $query = entity()->createQuery($dql);
            // $query->setMaxResults(5);
            // $books = $query->getResult();
            // dd($books);

            // $db = model('book');
            // $test = $db->where('offer_id > 0')->only('offer');
            // $all = $db->where('offer_id > 0')->limit(1)->select('offer', true);
            // $b = $db->where('id = 1')->select('offer_id', true);
            // if ($b->count()) {
            //     foreach ($b as $book) {
            //         dd($book->save()->assoc());
            //     }
            // }

            // $dbc = nosql('test', 'country');
            // $c = $db->create()->setName('France')->save();
            // $db = nosql('test', 'product');
            // $p = $db->create()->setName('TV')->linkCountry($dbc->find(1))->setPrice(299)->save();
            // dd($db->find(2));

            // $db = model('book');
            // $all = $db->find(14);
            // dd($all);

            // $dbProduct = em('core', 'product');
            // $dbCountry = em('core', 'country');
            // $record = $dbProduct->create(array('country_id' => 'relation'))->setName('truc')->setPrice(21)->linkCountry($dbCountry->create()->setName('France')->save())->save();
            // dd($dbProduct->fetch()->exec(true));

            // $dbProduct = container()->nbm('product');
            // $dbCountry = container()->nbm('country');
            // $record = $dbProduct->create(array('country_id' => 'relation'))->setName('truc')->setPrice(21)->linkCountry($dbCountry->create()->setName('France')->save())->save();
            // dd($record->country());

            // $dbProduct = container()->tbm('product');
            // $dbCountry = container()->tbm('country');
            // $record = $dbProduct->create(array('country_id' => 'relation'))->setName('truc')->setPrice(21)->linkCountry($dbCountry->create()->setName('France')->save())->save();
            // dd($record->country());

            // dd(container()->bucket()->backup(__FILE__));

            // $db = model('book');
            // $res = $db->field('offer.price')->named('price')->join(model('offer'))->where('id < 100')->where('offer.price > 10')->order('name', 'DESC')->order('offer.price')->first(true)->setDescription(1)->save(false);
            // $res = $db->likeName('chou')->exec();
            // $res = $db->cache(1200)->in('1,11')->exec(true)->extend('test', function () {
            //     return time();
            // })->first();
            // $res = $res->fn('test', function () {
            //     return time();
            // });
            // dd($res->test());
            // dd($res->offer()->books()->last()->assoc());
            // dd($res->one('offer'));
            // $app = phalcon();
            // phalconModel('book');
            // $b = Models\Book::find(11);
            // dd($app->handle()->getContent());
            // dd($b->id);
            // dd($app);

            // $db = model('book');
            // dd($db->find(1)->offer()->price());
            // dd(model('offer')->find(24)->books()->where('id < 500')->exec());
            // dd(model('offer')->find(24)->books()->groupBy('offer_id')->exec());
            // $books = $db
            // ->offer(model('offer')->find(24))
            // ->offer(model('offer')->find(21), 'XOR')
            // ->exec();
            // $books = $db
            // ->join(model('offer'))
            // ->isNotNull('description')
            // ->limit(5,10)
            // ->exec();
            // $offer = $db->find(1)->offer()->orm()->find();
            // $offer = $db->find(17)->offer()->books();
            // $offers = $book->offers(false);
            // dd($offer->last()->assoc());
            // dd($offers);
            // $s = session('test', 'redis');
            // $s->setTest('ok');
            // dd($s->getTest());
            // dd(validator(model('book')));
            // dd(instance('Thin\\utils', array(array('time' => time()))));

            // $service = with(new Provider\Service)->register('mail');
            // dd($service);
            // dd(Config::get('database'));
            // $cron = Cron::instance();
            // $c = $cron->queue('test');
            // dd($cron->flush());

            // $app = App::instance();
            // $lang = $app->getLang();
            // dd($lang->translate('dd', 'coucou'));
            // dd($app->getLang());
            // $app->setMachin('ddg')->setTruc('dd');
            // $app->event('machin', function ($p) {return md5($p);});
            // vd(context()->get('app')->machin('truc'));
            // vd($app->machin('fff'));
            // dd($app);
            // View::cleanCache();
            // dd(url()->to('test', array('super' => 12, 'variable_test' => 20)));
            // dd(url()->link('test', 'ok', array('class' => 'link'), array('super' => 12, 'variable_test' => 20)));

            // events()->listen('truc', function() {return true;});
            // events()->listen('machin', function() {return false;});
            // dd(events());

            // $db = model('user');
            // dd($db->full()->orderByPartner()->exec());
            // dd($db->findOneObjectByEmail("clara1989@free.fr"));
            // $db = model('book');
            // dd($db->fields('name, offer_id')->find(1, false));
            // dd($db->field('offer_id')->find(1, false));
            // $db->listenEvent('truc', function() {return true;});
            // dd($db->fireEvent('truc'));
            // dd($db->getObservableEvents());
            // $url = new \Phalcon\Mvc\Url();
            // dd(url()->make("products/save"));
            // dd(container()->bucket());
        }
    }
