<?php
    namespace Dbjson;
    use Closure;
    use Thin\Utils;
    use Thin\Exception;
    use Thin\Instance;
    use Thin\File;
    use Thin\Arrays;
    use Thin\Inflector;
    use Thin\Route;
    use Thin\Container;
    use Thin\Database\Collection;

    class Dbjson
    {
        public $dir, $model, $db, $table, $results;
        public $wheres = array();
        public $keys   = array();
        public static $config = array();

        public function __construct()
        {
            $args = func_get_args();
            $path = STORAGE_PATH;
            // $path = isAke(get_defined_constants(), 'STORAGE_PATH', false);

            if (0 == count($args)) {
                return;
            } elseif (2 == count($args)) {
                list($db, $table) = $args;
            } elseif (3 == count($args)) {
                list($db, $table, $path) = $args;
            }

            if (false === $path) {
                throw new Exception("You must provide a path in third argument of this method.");
            }

            if (!is_dir($path . DS . 'dbjson')) {
                umask(0000);
                File::mkdir($path . DS . 'dbjson', 0777, true);
            }

            $this->dir  = $path . DS . 'dbjson' . DS . Inflector::lower($db) . DS . Inflector::lower($table);

            if (!is_dir($path . DS . 'dbjson' . DS . Inflector::lower($db))) {
                umask(0000);
                File::mkdir($path . DS . 'dbjson' . DS . Inflector::lower($db), 0777, true);
            }

            if (!is_dir($path . DS . 'dbjson' . DS . Inflector::lower($db) . DS . Inflector::lower($table))) {
                umask(0000);
                File::mkdir($path . DS . 'dbjson' . DS . Inflector::lower($db) . DS . Inflector::lower($table), 0777, true);
            }

            $changeFile = $this->dir . DS  . 'change';

            if (!File::exists($changeFile)) {
                File::put($changeFile, '');
            }

            $this->db       = $db;
            $this->table    = $table;
        }

        public static function instance()
        {
            $args   = func_get_args();
            $key    = sha1(serialize($args));
            $has    = Instance::has('Dbjson', $key);

            if (true === $has) {
                return Instance::get('Dbjson', $key);
            } else {
                if (count($args) == 2) {
                    list($db, $table) = $args;
                    return Instance::make('Dbjson', $key, new self($db, $table));
                } elseif (count($args) == 3) {
                    list($db, $table, $path) = $args;
                    return Instance::make('Dbjson', $key, new self($db, $table, $path));
                } else {
                    return Instance::make('Dbjson', $key, new self);
                }
            }
        }

        public function pk()
        {
            return 'id';
        }

        public function countAll()
        {
            return count($this->all(true));
        }

        public function count()
        {
            $count = count($this->results);
            $this->reset();

            return $count;
        }

        public function post($save = false)
        {
            return !$save ? $this->create($_POST) : $this->create($_POST)->save();
        }

        public function save($data, $object = true)
        {
            $changeFile = $this->dir . DS  . 'change';
            File::delete($changeFile);
            File::put($changeFile, '');

            if (is_object($data) && $data instanceof Container) {
                $data = $data->assoc();
            }

            $id = isAke($data, 'id', null);

            if (strlen($id)) {
                return $this->edit($id, $data, $object);
            } else {
                return $this->add($data, $object);
            }
        }

        private function add($data, $object = true)
        {
            if (!Arrays::is($data)) {
                return $data;
            }

            $this->lastInsertId = $this->makeId();
            $data['id'] = $this->lastInsertId;
            $data['created_at'] = $data['updated_at'] = time();
            $file  = $this->dir . DS . $this->lastInsertId . '.row';

            foreach ($data as $k => $v) {
                if ($v instanceof Closure) {
                    unset($data[$k]);
                }
            }

            File::put($file, json_encode($data));

            return true === $object ? $this->row($data) : $data;
        }

        private function edit($id, $data, $object = true)
        {
            if (!Arrays::is($data)) {
                return $data;
            }

            $data['id'] = $id;
            $data['updated_at'] = time();

            $old    = $this->find($id)->assoc();
            $new    = array_merge($old, $data);

            $file   = $this->dir . DS . $id . '.row';

            foreach ($data as $k => $v) {
                if ($v instanceof Closure) {
                    unset($data[$k]);
                }
            }

            File::put($file, json_encode($new));

            return true === $object ? $this->row($new) : $new;
        }

        public function deleteRow($id)
        {
            $changeFile = $this->dir . DS  . 'change';
            File::delete($changeFile);
            File::put($changeFile, '');

            $file  = $this->dir . DS . $id . '.row';
            File::delete($file);

            return $this;
        }

        public function delete($where = null)
        {
            if (is_null($where)) {
                return $this->exec(true)->delete();
            } else {
                return $this->where($where)->exec(true)->delete();
            }
        }

        public function all($object = false)
        {
            $allFile = $this->dir . DS  . 'all';
            $changeFile = $this->dir . DS  . 'change';

            if (File::exists($changeFile) && File::exists($allFile)) {
                $ageAll     = filemtime($allFile);
                $ageChange  = filemtime($changeFile);

                if ($ageAll > $ageChange) {
                    $collection = json_decode(file_get_contents($allFile), true);

                    return true === $object ? new Collection($collection) : $collection;
                } else {
                    File::delete($allFile);
                }
            }

            $collection = array();
            $rows       = glob($this->dir . DS . '*.row', GLOB_NOSORT);

            if (count($rows)) {
                foreach ($rows as $row) {
                    $data = json_decode(file_get_contents($row), true);

                    if (true === $object) {
                        $data = $this->row($data);
                    }
                    array_push($collection, $data);
                }

                File::put($allFile, json_encode($collection));
            }

            return true === $object ? new Collection($collection) : $collection;
        }

        public function fetch($object = false)
        {
            $this->results = $this->all($object);

            return $this;
        }

        public function full()
        {
            $this->results = $this->all(false);

            return $this;
        }

        public function execute($object = false)
        {
            return $this->exec($object);
        }

        public function exec($object = false)
        {
            $collection = array();

            if (count($this->results)) {
                foreach ($this->results as $row) {
                    $item = true === $object ? $this->row($row) : $row;
                    array_push($collection, $item);
                }
            }

            $this->reset();

            return true === $object ? new Collection($collection) : $collection;
        }

        public function update(array $updates, $where = null)
        {
            $res = !empty($where) ? $this->where($where)->exec() : $this->all();

            if (count($res)) {
                if (count($updates)) {
                    foreach ($updates as $key => $newValue) {
                        foreach ($res as $row) {
                            $val = isAke($row, $field, null);

                            if ($val != $newValue) {
                                $row[$field] = $newValue;
                                $this->edit($row['id'], $row);
                            }
                        }
                    }
                }
            }

            return $this;
        }

        public function flushAll()
        {
            return $this->remove();
        }

        public function remove($where = null)
        {
            $res = !empty($where) ? $this->where($where)->exec() : $this->all();

            if (count($res)) {
                foreach ($res as $row) {
                    $this->deleteRow($row['id']);
                }
            }

            return $this;
        }

        public function groupBy($field, $results = array())
        {
            $res = count($results) ? $results : $this->results;
            $groupBys   = array();
            $ever       = array();

            foreach ($res as $id => $tab) {
                $obj = isAke($tab, $field, null);

                if (!Arrays::in($obj, $ever)) {
                    $groupBys[$id]  = $tab;
                    $ever[]         = $obj;
                }
            }

            $this->results = $groupBys;
            $this->order($field);

            return $this;
        }

        public function limit($limit, $offset = 0, $results = array())
        {
            $res            = count($results) ? $results : $this->results;
            $offset         = count($res) < $offset ? count($res) : $offset;
            $this->results  = array_slice($res, $offset, $limit);

            return $this;
        }

        public function sum($field, $results = array())
        {
            $res = count($results) ? $results : $this->results;
            $sum = 0;

            if (count($res)) {
                foreach ($res as $id => $tab) {
                    $val = isAke($tab, $field, 0);
                    $sum += $val;
                }
            }

            $this->reset();

            return (int) $sum;
        }

        public function avg($field, $results = array())
        {
            return (float) $this->sum($field, $results) / count($results);
        }

        public function min($field, $results = array())
        {
            $res = count($results) ? $results : $this->results;
            $min = 0;

            if (count($res)) {
                $first = true;

                foreach ($res as $id => $tab) {
                    $val = isAke($tab, $field, 0);

                    if (true === $first) {
                        $min = $val;
                    } else {
                        $min = $val < $min ? $val : $min;
                    }

                    $first = false;
                }
            }

            $this->reset();

            return $min;
        }

        public function max($field, $results = array())
        {
            $res = count($results) ? $results : $this->results;
            $max = 0;

            if (count($res)) {
                $first = true;

                foreach ($res as $id => $tab) {
                    $val = isAke($tab, $field, 0);

                    if (true === $first) {
                        $max = $val;
                    } else {
                        $max = $val > $max ? $val : $max;
                    }

                    $first = false;
                }
            }

            $this->reset();

            return $max;
        }

        public function rand($results = array())
        {
            $res = count($results) ? $results : $this->results;
            shuffle($res);
            $this->results = $res;

            return $this;
        }

        public function order($fieldOrder, $orderDirection = 'ASC', $results = array())
        {
            $res = count($results) ? $results : $this->results;

            if (empty($res)) {
                return $this;
            }

            $sortFunc = function($key, $direction) {
                return function ($a, $b) use ($key, $direction) {
                    if ('ASC' == $direction) {
                        return $a[$key] > $b[$key];
                    } else {
                        return $a[$key] < $b[$key];
                    }
                };
            };

            if (Arrays::is($fieldOrder) && !Arrays::is($orderDirection)) {
                $t = array();

                foreach ($fieldOrder as $tmpField) {
                    array_push($t, $orderDirection);
                }

                $orderDirection = $t;
            }

            if (!Arrays::is($fieldOrder) && Arrays::is($orderDirection)) {
                $orderDirection = Arrays::first($orderDirection);
            }

            if (Arrays::is($fieldOrder) && Arrays::is($orderDirection)) {
                for ($i = 0 ; $i < count($fieldOrder) ; $i++) {
                    usort($res, $sortFunc($fieldOrder[$i], $orderDirection[$i]));
                }
            } else {
                usort($res, $sortFunc($fieldOrder, $orderDirection));
            }

            $this->results = $res;

            return $this;
        }

        public function andWhere($condition, $results = array())
        {
            return $this->where($condition, 'AND', $results);
        }

        public function orWhere($condition, $results = array())
        {
            return $this->where($condition, 'OR', $results);
        }

        public function xorWhere($condition, $results = array())
        {
            return $this->where($condition, 'XOR', $results);
        }

        public function _and($condition, $results = array())
        {
            return $this->where($condition, 'AND', $results);
        }

        public function _or($condition, $results = array())
        {
            return $this->where($condition, 'OR', $results);
        }

        public function _xor($condition, $results = array())
        {
            return $this->where($condition, 'XOR', $results);
        }

        public function whereAnd($condition, $results = array())
        {
            return $this->where($condition, 'AND', $results);
        }

        public function whereOr($condition, $results = array())
        {
            return $this->where($condition, 'OR', $results);
        }

        public function whereXor($condition, $results = array())
        {
            return $this->where($condition, 'XOR', $results);
        }

        public function between($field, $min, $max, $object = false)
        {
            return $this->where($field . ' >= ' . $min)->where($field . ' <= ' . $max)->exec($object);
        }

        public function firstOrNew($tab = array())
        {
            return $this->firstOrCreate($tab, false);
        }

        public function firstOrCreate($tab = array(), $save = true)
        {
            if (count($tab)) {
                foreach ($tab as $key => $value) {
                    $this->where("$key = $value");
                }

                $first = $this->first(true);

                if (!is_null($first)) {
                    return $first;
                }
            }

            $item = $this->create($tab);

            return !$save ? $item : $item->save();
        }

        public function replace($compare = array(), $update = array())
        {
            $instance = $this->firstOrCreate($compare);

            return $instance->hydrate($update)->save();
        }

        public function create($tab = array())
        {
            $tab['created_at'] = isAke($tab, 'created_at', time());
            $tab['updated_at'] = isAke($tab, 'updated_at', time());

            return $this->row($tab);
        }

        public function row($tab = array())
        {
            $o = new Container;
            $o->populate($tab);

            return $this->closures($o);
        }

        public function rows()
        {
            return $this->exec();
        }

        private function closures($obj)
        {
            $db = $this;
            $db->results = null;
            $db->wheres = null;

            $save = function () use ($obj, $db) {
                return $db->save($obj);
            };

            $database = function () use ($db) {
                return $db;
            };

            $delete = function () use ($obj, $db) {
                return $db->deleteRow($obj->id);
            };

            $id = function () use ($obj) {
                return $obj->id;
            };

            $exists = function () use ($obj) {
                return isset($obj->id);
            };

            $touch = function () use ($obj) {
                if (!isset($obj->created_at))  $obj->created_at = time();
                $obj->updated_at = time();

                return $obj;
            };

            $duplicate = function () use ($obj) {
                if (isset($obj->id)) unset($obj->id);
                if (isset($obj->created_at)) unset($obj->created_at);

                return $obj->save();
            };

            $hydrate = function ($data = array()) use ($obj) {
                $data = empty($data) ? $_POST : $data;

                if (Arrays::isAssoc($data)) {
                    foreach ($data as $k => $v) {
                        if ('true' == $v) {
                            $v = true;
                        } elseif ('false' == $v) {
                            $v = false;
                        } elseif ('null' == $v) {
                            $v = null;
                        }
                        $obj->$k = $v;
                    }
                }

                return $obj;
            };

            $date = function ($f) use ($obj) {
                return date('Y-m-d H:i:s', $obj->$f);
            };

            $obj->event('save', $save)
            ->event('delete', $delete)
            ->event('date', $date)
            ->event('exists', $exists)
            ->event('id', $id)
            ->event('db', $database)
            ->event('touch', $touch)
            ->event('hydrate', $hydrate)
            ->event('duplicate', $duplicate);

            $settings   = isAke(self::$config, "$this->db.$this->table");
            $functions  = isAke($settings, 'functions');

            if (count($functions)) {
                foreach ($functions as $closureName => $callable) {
                    $closureName    = lcfirst(Inflector::camelize($closureName));
                    $share          = function () use ($obj, $callable, $db) {
                        $args[]     = $obj;
                        $args[]     = $db;
                        return call_user_func_array($callable, $args);
                    };
                    $obj->event($closureName, $share);
                }
            }

            return $this->related($obj);
        }

        private function related(Container $obj)
        {
            $fields = array_keys($obj->assoc());

            foreach ($fields as $field) {
                if (endsWith($field, '_id')) {
                    if (is_string($field)) {
                        $value = $obj->$field;

                        if (!is_callable($value)) {
                            $fk = repl('_id', '', $field);
                            $ns = $this->db;
                            $cb = function() use ($value, $fk, $ns) {
                                $db = jdb($ns, $fk);

                                return $db->find($value);
                            };

                            $obj->event($fk, $cb);

                            $setter = lcfirst(Inflector::camelize("link_$fk"));

                            $cb = function(Container $fkObject) use ($obj, $field, $fk) {
                                $obj->$field = $fkObject->getId();
                                $newCb = function () use ($fkObject) {
                                    return $fkObject;
                                };
                                $obj->event($fk, $newCb);

                                return $obj;
                            };

                            $obj->event($setter, $cb);
                        }
                    }
                }
            }

            return $obj;
        }

        public function find($id, $object = true)
        {
            $file = $this->dir . DS . $id . '.row';
            $row = File::exists($file) ? fgc($file) : '';

            if (strlen($row)) {
                $tab = json_decode($row, true);

                return $object ? $this->row($tab) : $tab;
            }

            return $object ? null : array();
        }

        public function findOneBy($field, $value, $object = false)
        {
            return $this->findBy($field, $value, true, $object);
        }

        public function findOrFail($id, $object = true)
        {
            if (!is_null($item = $this->find($id, $object))) {
                return $item;
            }

            throw new Exception("Row '$id' in '$this->table' is unknown.");
        }

        public function findBy($field, $value, $one = false, $object = false)
        {
            $res = $this->search("$field = $value");

            if (count($res) && true === $one) {
                return $object ? $this->row(Arrays::first($res)) : Arrays::first($res);
            }

            if (!count($res) && true === $one && true === $object) {
                return null;
            }

            return $this->exec($object);
        }

        public function one($object = true)
        {
            return $this->first($object);
        }

        public function object()
        {
            return $this->first(true);
        }

        public function objects()
        {
            return $this->exec(true);
        }

        public function first($object = false)
        {
            $res = isset($this->results) ? $this->results : $this->all($object);
            $this->reset();

            if (true === $object) {
                return count($res) ? $this->row(Arrays::first($res)) : null;
            } else {
                return count($res) ? Arrays::first($res) : array();
            }
        }

        public function fields()
        {
            $row = $this->first();

            if (!empty($row)) {
                unset($row['created_at']);
                unset($row['updated_at']);
                ksort($row);

                return array_keys($row);
            }

            return array('id');
        }

        public function only($field)
        {
            $row = $this->first(true);

            return $row instanceof Container ? $row->$field : null;
        }

        public function select($fields, $object = false)
        {
            $collection = array();
            $fields = Arrays::is($fields) ? $fields : array($fields);
            $rows = $this->exec($object);

            if (true === $object) {
                $rows = $rows->rows();
            }

            if (count($rows)) {
                foreach ($rows as $row) {
                    $record = true === $object
                    ? $this->row(
                        array(
                            'id' => $row->id,
                            'created_at' => $row->created_at,
                            'updated_at' => $row->updated_at
                        )
                    )
                    : array();

                    foreach ($fields as $field) {
                        if (true === $object) {
                            $record->$field = $row->$field;
                        } else {
                            $record[$field] = $row[$field];
                        }
                    }
                    array_push($collection, $record);
                }
            }

            return true === $object ? new Collection($collection) : $collection;
        }

        public function last($object = false)
        {
            $res = $this->results;
            $this->reset();

            if (true === $object) {
                return count($res) ? $this->row(Arrays::last($res)) : null;
            } else {
                return count($res) ? Arrays::last($res) : array();
            }
        }

        private function intersect($tab1, $tab2)
        {
            $ids1       = array();
            $ids2       = array();
            $collection = array();

            foreach ($tab1 as $row) {
                $id = isAke($row, 'id', null);
                if (strlen($id)) {
                    array_push($ids1, $id);
                }
            }

            foreach ($tab2 as $row) {
                $id = isAke($row, 'id', null);
                if (strlen($id)) {
                    array_push($ids2, $id);
                }
            }

            $sect = array_intersect($ids1, $ids2);

            if (count($sect)) {
                foreach ($sect as $idRow) {
                    array_push($collection, $this->find($idRow, false));
                }
            }

            return $collection;
        }

        public function query($sql)
        {
            if (strstr($sql, ' && ')) {
                $segs = explode(' && ', $sql);

                foreach ($segs as $seg) {
                    $this->where($seg);
                    $sql = str_replace($seg . ' && ', '', $sql);
                }
            }

            if (strstr($sql, ' || ')) {
                $segs = explode(' || ', $sql);

                foreach ($segs as $seg) {
                    $this->where($seg, 'OR');
                    $sql = str_replace($seg . ' || ', '', $sql);
                }
            }

            if (!empty($sql)) {
                $this->where($sql);
            }

            return $this;
        }

        public function in($ids, $field = null)
        {
            /* polymorphism */
            $ids = !Arrays::is($ids)
            ? strstr($ids, ',')
                ? explode(',', repl(' ', '', $ids))
                : array($ids)
            : $ids;

            $field = is_null($field) ? 'id' : $field;

            return $this->where($field . ' IN (' . implode(',', $ids) . ')');
        }

        public function notIn($ids, $field = null)
        {
            /* polymorphism */
            $ids = !Arrays::is($ids)
            ? strstr($ids, ',')
                ? explode(',', repl(' ', '', $ids))
                : array($ids)
            : $ids;

            $field = is_null($field) ? 'id' : $field;

            return $this->where($field . ' NOT IN (' . implode(',', $ids) . ')');
        }

        public function like($field, $str, $op = 'AND')
        {
            return $this->where("$field LIKE " . $str, $op);
        }

        public function trick(Closure $condition, $op = 'AND', $results = array())
        {
            $data = !count($results) ? $this->all() : $results;
            $res = array();

            if (count($data)) {
                foreach ($data as $row) {
                    $resTrick = $condition($row);

                    if (true === $resTrick) {
                        array_push($res, $row);
                    }
                }
            }

            if (!count($this->wheres)) {
                $this->results = array_values($res);
            } else {
                $values = array_values($this->results);

                switch ($op) {
                    case 'AND':
                        $this->results = $this->intersect($values, array_values($res));
                        break;
                    case 'OR':
                        $this->results = array_merge($values, array_values($res));
                        break;
                    case 'XOR':
                        $this->results = array_merge(
                            array_diff(
                                $values,
                                array_values($res),
                                array_diff(
                                    array_values($res),
                                    $values
                                )
                            )
                        );
                        break;
                }
            }

            $this->wheres[] = $condition;

            return $this;
        }

        public function where($condition, $op = 'AND', $results = array())
        {
            $res = $this->search($condition, $results, false);

            if (!count($this->wheres)) {
                $this->results = array_values($res);
            } else {
                $values = array_values($this->results);

                switch ($op) {
                    case 'AND':
                        $this->results = $this->intersect($values, array_values($res));
                        break;
                    case 'OR':
                        $this->results = array_merge($values, array_values($res));
                        break;
                    case 'XOR':
                        $this->results = array_merge(
                            array_diff(
                                $values,
                                array_values($res),
                                array_diff(
                                    array_values($res),
                                    $values
                                )
                            )
                        );
                        break;
                }
            }

            $this->wheres[] = $condition;

            return $this;
        }

        private function search($condition = null, $results = array(), $populate = true)
        {
            $searchFile = $this->dir . DS  . sha1(serialize(func_get_args()));
            $changeFile = $this->dir . DS  . 'change';

            if (File::exists($changeFile) && File::exists($searchFile)) {
                $ageSearch  = filemtime($searchFile);
                $ageChange  = filemtime($changeFile);

                if ($ageSearch > $ageChange) {
                    $collection = json_decode(file_get_contents($searchFile), true);

                    if (true === $populate) {
                        $this->results = $collection;
                    }

                    return $collection;
                } else {
                    File::delete($searchFile);
                }
            }

            $datas = !count($results) ? $this->all() : $results;

            if (empty($condition)) {
                return $datas;
            }

            $collection = array();

            $condition  = repl('LIKE START', 'LIKESTART', $condition);
            $condition  = repl('LIKE END', 'LIKEEND', $condition);
            $condition  = repl('NOT LIKE', 'NOTLIKE', $condition);
            $condition  = repl('NOT IN', 'NOTIN', $condition);

            list($field, $op, $value) = explode(' ', $condition, 3);

            if ($value instanceof Container) {
                $value = $value->id();
                $field = $field . '_id';
            }

            if(count($datas)) {
                foreach ($datas as $tab) {
                    if (!empty($tab)) {
                        $val = isAke($tab, $field, null);

                        if (strlen($val)) {
                            $val = repl('|', ' ', $val);
                            $check = $this->compare($val, $op, $value);
                        } else {
                            $check = ('null' == $value) ? true : false;
                        }

                        if (true === $check) {
                            array_push($collection, $tab);
                        }
                    }
                }
            }

            if (true === $populate) {
                $this->results = $collection;
            }

            File::put($searchFile, json_encode($collection));

            return $collection;
        }

        private function compare($comp, $op, $value)
        {
            $res = false;

            if (isset($comp)) {
                $comp   = Inflector::lower($comp);
                $value  = Inflector::lower($value);

                switch ($op) {
                    case '=':
                        $res = sha1($comp) == sha1($value);
                        break;
                    case '>=':
                        $res = $comp >= $value;
                        break;
                    case '>':
                        $res = $comp > $value;
                        break;
                    case '<':
                        $res = $comp < $value;
                        break;
                    case '<=':
                        $res = $comp <= $value;
                        break;
                    case '<>':
                    case '!=':
                        $res = sha1($comp) != sha1($value);
                        break;
                    case 'LIKE':
                        $value = repl("'", '', $value);
                        $value = repl('%', '', $value);
                        if (strstr($comp, $value)) {
                            $res = true;
                        }
                        break;
                    case 'NOTLIKE':
                        $value = repl("'", '', $value);
                        $value = repl('%', '', $value);
                        if (!strstr($comp, $value)) {
                            $res = true;
                        }
                        break;
                    case 'LIKESTART':
                        $value = repl("'", '', $value);
                        $value = repl('%', '', $value);
                        $res = (substr($comp, 0, strlen($value)) === $value);
                        break;
                    case 'LIKEEND':
                        $value = repl("'", '', $value);
                        $value = repl('%', '', $value);
                        if (!strlen($comp)) {
                            $res = true;
                        }
                        $res = (substr($comp, -strlen($value)) === $value);
                        break;
                    case 'IN':
                        $value = repl('(', '', $value);
                        $value = repl(')', '', $value);
                        $tabValues = explode(',', $value);
                        $res = Arrays::in($comp, $tabValues);
                        break;
                    case 'NOTIN':
                        $value = repl('(', '', $value);
                        $value = repl(')', '', $value);
                        $tabValues = explode(',', $value);
                        $res = !Arrays::in($comp, $tabValues);
                        break;
                }
            }

            return $res;
        }

        public function extend($name, $callable)
        {
            $settings   = isAke(self::$config, "$this->db.$this->table");
            $functions  = isAke($settings, 'functions');

            $functions[$name] = $callable;

            self::$config["$this->db.$this->table"]['functions'] = $functions;

            return $this;
        }

        private function getKeys()
        {
            if (empty($this->keys)) {
                $ids = array();
                $data = glob($this->dir . DS . '*.row', GLOB_NOSORT);

                if (count($data)) {
                    foreach ($data as $row) {
                        $id = str_replace(
                            array(
                                $this->dir . DS,
                                '.row'
                            ),
                            '',
                            $row
                        );
                        array_push($ids, $id);
                    }
                }

                return $ids;
            }

            return $this->keys;
        }

        public function reset()
        {
            $this->results          = null;
            $this->wheres           = array();

            return $this;
        }

        private function makeId()
        {
            $ids = $this->getKeys();

            if (!count($ids)) {
                $id = 1;
            } else {
                $max = intval(max($ids));
                $id = $max + 1;
            }

            array_push($this->keys, $id);

            return $id;
        }

        /* API static */

        public static function keys($pattern)
        {
            $collection = array();
            $db = static::instance('core', 'core');
            $pattern = repl('*', '', $pattern);

            return $db->where("key LIKE '$pattern'")->exec(true);
        }

        public static function get($key, $default = null, $object = false)
        {
            static::clean();
            $db = static::instance('core', 'core');
            $value = $db->where("key = $key")->first(true);

            return $value instanceof Container ? false === $object ? $value->getValue() : $value : $default;
        }

        public static function set($key, $value, $expire = 0)
        {
            $db     = static::instance('core', 'core');
            $exists = self::get($key, null, true);

            if (0 < $expire) $expire += time();

            if ($exists instanceof Container) {
                return $exists->setValue($value)->setExpire($expire)->save();
            } else {
                return $db->create()->setKey($key)->setValue($value)->setExpire($expire)->save();
            }
        }

        public static function del($key)
        {
            $db = static::instance('core', 'core');
            $exists = $db->where("key = $key")->first(true);

            if ($exists instanceof Container) {
                $exists->delete();
            }

            return $db;
        }

        public static function incr($key, $by = 1)
        {
            $val = self::get($key);

            if (!strlen($val)) {
                $val = 1;
            } else {
                $val = (int) $val;
                $val += $by;
            }

            self::set($key, $val);

            return $val;
        }

        public static function decr($key, $by = 1)
        {
            $val = self::get($key);

            if (!strlen($val)) {
                $val = 0;
            } else {
                $val = (int) $val;
                $val -= $by;
                $val = 0 > $val ? 0 : $val;
            }

            self::set($key, $val);

            return $val;
        }

        public static function expire($key, $expire = 0)
        {
            $db = static::instance('core', 'core');
            $exists = $db->where("key = $key")->first(true);

            if ($exists instanceof Container) {
                if (0 < $expire) $expire += time();
                $exists->setExpire($expire)->save();

                return true;
            }

            return false;
        }

        public static function clean()
        {
            $db = static::instance('core', 'core');

            return $db->where('expire > 0')->where('expire < ' . time())->exec(true)->delete();
        }

        private static function structure($ns, $table, $fields)
        {
            $dbt = jmodel('jma_table');
            $dbf = jmodel('jma_field');
            $dbs = jmodel('jma_structure');

            $t = $dbt->where('name = ' . $table)->where('ns = ' . $ns)->first(true);

            if (is_null($t)) {
                $t = $dbt->create(array('name' => $table, 'ns' => $ns))->save();
            }

            if (!is_null($t)) {
                if (count($fields)) {
                    foreach ($fields as $field) {
                        if ('id' != $field) {
                            $f = $dbf->where('name = ' . $field)->first(true);

                            if (is_null($f)) {
                                $f = $dbf->create()->setName($field)->save();
                            }

                            $s = $dbs
                            ->where('table = ' . $t->getId())
                            ->where('field = ' . $f->getId())
                            ->first(true);

                            if (is_null($s)) {
                                $s = $dbs->create()
                                ->setTable($t->getId())
                                ->setField($f->getId())
                                ->setType('varchar')
                                ->setLength(255)
                                ->setIsIndex(false)
                                ->setCanBeNull(true)
                                ->setDefault(null)
                                ->save();
                            }
                        }
                    }
                }
            }
        }

        public static function tables()
        {
            $dbt = jmodel('jma_table');
            $dirs = glob(STORAGE_PATH . DS . 'dbjson' . DS . '*', GLOB_NOSORT);
            $rows = array();

            if (count($dirs)) {
                foreach ($dirs as $dir) {
                    $tmp    = glob($dir . DS . '*', GLOB_NOSORT);
                    $rows   = array_merge($rows, $tmp);
                }
            }

            $tables = array();

            if (count($rows)) {
                foreach ($rows as $row) {
                    $tab = explode(DS, $row);
                    $index = Arrays::last($tab);
                    $ns = $tab[count($tab) - 2];

                    if (!strstr($index, 'jma_')) {
                        $t = $dbt->where('name = ' . $index)->where('ns = ' . $ns)->first(true);

                        if (is_null($t)) {
                            $total = count(glob(STORAGE_PATH . DS . 'dbjson' . DS . $ns . DS . $index . DS . '*.row', GLOB_NOSORT));

                            $tables[$index]['count']    = $total;
                            $data                       = jmodel($index, $ns)->fetch()->exec();

                            if (count($data)) {
                                $first = Arrays::first($data);
                                $fields = array_keys($first);
                                $tables[$index]['fields'] = $fields;
                            } else {
                                $fields = array();
                            }

                            self::structure($ns, $index, $fields);
                        }
                    }
                }
            }
            return $tables;
        }

        public function createTable()
        {
            return $this;
        }

        public function dropTable()
        {
            File::rmdir($this->dir);

            return $this;
        }

        public function emptyTable()
        {
            $rows = $this->fetch()->exec();

            if (count($rows)) {
                foreach ($rows as $row) {
                    $this->deleteRow($row['id']);
                }
            }

            return $this;
        }

        public function config($key, $value = null)
        {
            self::configs("$this->db.$this->table", $key, $value);
        }

        public static function configs($entity, $key, $value = null, $cb = null)
        {
            if (!strlen($entity)) {
                throw new Exception("An entity must be provided to use this method.");
            }

            if (!Arrays::exists($entity, static::$config)) {
                self::$config[$entity] = array();
            }

            if (empty($value)) {
                if (!strlen($key)) {
                    throw new Exception("A key must be provided to use this method.");
                }

                return isAke(self::$config[$entity], $key, null);
            }

            if (!strlen($key)) {
                throw new Exception("A key must be provided to use this method.");
            }

            $reverse = strrev($key);
            $last = $reverse{0};

            if ('s' == $last) {
                self::$config[$entity][$key] = $value;
            } else {
                if (!Arrays::exists($key . 's', self::$config[$entity])) {
                    self::$config[$entity][$key . 's'] = array();
                }
                array_push(self::$config[$entity][$key . 's'], $value);
            }

            return !is_callable($cb) ? true : $cb();
        }

        public function export($q = null, $type = 'csv')
        {
            if (!empty($this->wheres)) {
                $datas = $this->results;
            } else {
                if (!empty($q)) {
                    $this->wheres[] = $q;
                    $datas = $this->search($q);
                } else {
                    $datas = $this->all(true);
                }
            }

            if (count($datas)) {
                $fields     = $this->fields();
                $rows = array();
                $rows[] = implode(';', $fields);

                foreach ($datas as $row) {
                    $tmp = array();

                    foreach ($fields as $field) {
                        $value = isAke($row, $field, null);
                        $tmp[] = $value;
                    }
                    $rows[] = implode(';', $tmp);
                }

                $this->$type($rows);
            } else {
                if (count($this->wheres)) {
                    $this->reset();
                    die('This query has no result.');
                } else {
                    die('This database is empty.');
                }
            }
        }

        private function csv($data)
        {
            $csv    = implode("\n", $data);
            $name   = date('d_m_Y_H_i_s') . '_' . $this->table . '_export.csv';
            $file   = TMP_PUBLIC_PATH . DS . $name;

            File::delete($file);
            File::put($file, $csv);
            Utils::go(repl('jma.php', '', URLSITE) . 'tmp/' . $name);
        }

        public static function __callStatic($fn, $args)
        {
            $method     = Inflector::uncamelize($fn);
            $tab        = explode('_', $method);
            $table      = array_shift($tab);
            $function   = implode('_', $tab);
            $function   = lcfirst(Inflector::camelize($function));
            $instance   = static::instance('core', $table);

            return call_user_func_array(array($instance, $function), $args);
        }

        public function __call($fn, $args)
        {
            $fields = $this->fields();

            $method = substr($fn, 0, 2);
            $object = lcfirst(substr($fn, 2));

            if ('is' === $method && strlen($fn) > 2) {
                $field = Inflector::uncamelize($object);
                if (!Arrays::in($field, $fields)) {
                    $field = $field . '_id';
                    $model = Arrays::first($args);
                    if ($model instanceof Container) {
                        $idFk = $model->id();
                    } else {
                        $idFk = $model;
                    }
                    return $this->where("$field = $idFk");
                } else {
                    return $this->where($field . ' = ' . Arrays::first($args));
                }
            }

            $method = substr($fn, 0, 4);
            $object = lcfirst(substr($fn, 4));

            if ('orIs' === $method && strlen($fn) > 4) {
                $field = Inflector::uncamelize($object);
                if (!Arrays::in($field, $fields)) {
                    $field = $field . '_id';
                    $model = Arrays::first($args);
                    if ($model instanceof Container) {
                        $idFk = $model->id();
                    } else {
                        $idFk = $model;
                    }
                    return $this->where("$field = $idFk", 'OR');
                } else {
                    return $this->where($field . ' = ' . Arrays::first($args), 'OR');
                }
            } elseif('like' === $method && strlen($fn) > 4) {
                $field = Inflector::uncamelize($object);
                $op = count($args) == 2 ? Arrays::last($args) : 'AND';

                return $this->like($field, Arrays::first($args), $op);
            }

            $method = substr($fn, 0, 5);
            $object = lcfirst(substr($fn, 5));

            if (strlen($fn) > 5) {
                if ('where' == $method) {
                    $field = Inflector::uncamelize($object);

                    if (!Arrays::in($field, $fields)) {
                        $field = $field . '_id';
                        $model = Arrays::first($args);

                        if ($model instanceof Container) {
                            $idFk = $model->id();
                        } else {
                            $idFk = $model;
                        }

                        return $this->where("$field = $idFk");
                    } else {
                        return $this->where($field . ' ' . Arrays::first($args));
                    }
                } elseif ('xorIs' === $method) {
                    $field = Inflector::uncamelize($object);

                    if (!Arrays::in($field, $fields)) {
                        $field = $field . '_id';
                        $model = Arrays::first($args);

                        if ($model instanceof Container) {
                            $idFk = $model->id();
                        } else {
                            $idFk = $model;
                        }

                        return $this->where("$field = $idFk", 'XOR');
                    } else {
                        return $this->where($field . ' = ' . Arrays::first($args), 'XOR');
                    }
                } elseif ('andIs' === $method) {
                    $field = Inflector::uncamelize($object);

                    if (!Arrays::in($field, $fields)) {
                        $field = $field . '_id';
                        $model = Arrays::first($args);

                        if ($model instanceof Container) {
                            $idFk = $model->id();
                        } else {
                            $idFk = $model;
                        }

                        return $this->where("$field = $idFk");
                    } else {
                        return $this->where($field . ' = ' . Arrays::first($args));
                    }
                }
            }

            $method = substr($fn, 0, 6);
            $object = Inflector::uncamelize(lcfirst(substr($fn, 6)));

            if (strlen($fn) > 6) {
                if ('findBy' == $method) {
                    return $this->findBy($object, Arrays::first($args));
                }
            }


            $method = substr($fn, 0, 7);
            $object = lcfirst(substr($fn, 7));

            if (strlen($fn) > 7) {
                if ('orWhere' == $method) {
                    $field = Inflector::uncamelize($object);

                    if (!Arrays::in($field, $fields)) {
                        $field = $field . '_id';
                        $model = Arrays::first($args);

                        if ($model instanceof Container) {
                            $idFk = $model->id();
                        } else {
                            $idFk = $model;
                        }

                        return $this->where("$field = $idFk", 'OR');
                    } else {
                        return $this->where($field . ' ' . Arrays::first($args), 'OR');
                    }
                } elseif ('orderBy' == $method) {
                    $object = Inflector::uncamelize(lcfirst(substr($fn, 7)));

                    if ($object == 'id') {
                        $object = $this->pk();
                    }

                    if (!Arrays::in($object, $fields)) {
                        $object = Arrays::in($object . '_id', $fields) ? $object . '_id' : $object;
                    }

                    $direction = (count($args)) ? Arrays::first($args) : 'ASC';

                    return $this->order($object, $direction);
                } elseif ('groupBy' == $method) {
                    $object = Inflector::uncamelize(lcfirst(substr($fn, 7)));

                    if ($object == 'id') {
                        $object = $this->pk();
                    }

                    if (!Arrays::in($object, $fields)) {
                        $object = Arrays::in($object . '_id', $fields) ? $object . '_id' : $object;
                    }

                    return $this->groupBy($object);
                }
            }

            $method = substr($fn, 0, 9);
            $object = Inflector::uncamelize(lcfirst(substr($fn, 9)));

            if (strlen($fn) > 9) {
                if ('findOneBy' == $method) {
                    return $this->findOneBy($object, Arrays::first($args));
                }
            }

            $method = substr($fn, 0, 13);
            $object = Inflector::uncamelize(lcfirst(substr($fn, 13)));

            if (strlen($fn) > 13) {
                if ('findObjectsBy' == $method) {
                    return $this->findBy($object, Arrays::first($args), true);
                }
            }

            $method = substr($fn, 0, 15);
            $object = Inflector::uncamelize(lcfirst(substr($fn, 15)));

            if (strlen($fn) > 15) {
                if ('findOneObjectBy' == $method) {
                    return $this->findOneBy($object, Arrays::first($args), true);
                }
            }

            $method = substr($fn, 0, 8);
            $object = lcfirst(substr($fn, 8));

            if (strlen($fn) > 8) {
                if ('xorWhere' == $method) {
                    $field = Inflector::uncamelize($object);

                    if (!Arrays::in($field, $fields)) {
                        $field = $field . '_id';
                        $model = Arrays::first($args);

                        if ($model instanceof Container) {
                            $idFk = $model->id();
                        } else {
                            $idFk = $model;
                        }
                        return $this->where("$field = $idFk", 'XOR');
                    } else {
                        return $this->where($field . ' ' . Arrays::first($args), 'XOR');
                    }
                } elseif('andWhere' == $method) {
                    $field = Inflector::uncamelize($object);

                    if (!Arrays::in($field, $fields)) {
                        $field = $field . '_id';
                        $model = Arrays::first($args);

                        if ($model instanceof Container) {
                            $idFk = $model->id();
                        } else {
                            $idFk = $model;
                        }

                        return $this->where("$field = $idFk");
                    } else {
                        return $this->where($field . ' ' . Arrays::first($args));
                    }
                }
            } else {
                $field = $fn;
                $fieldFk = $fn . '_id';
                $op = count($args) == 2 ? Inflector::upper(Arrays::last($args)) : 'AND';

                if (Arrays::in($field, $fields)) {
                    return $this->where($field . ' = ' . Arrays::first($args), $op);
                } else if (Arrays::in($fieldFk, $fields)) {
                    $model = Arrays::first($args);

                    if ($model instanceof Container) {
                        $idFk = $model->id();
                    } else {
                        $idFk = $model;
                    }

                    return $this->where("$fieldFk = $idFk", $op);
                }
            }

            throw new Exception("Method '$fn' is unknown.");
        }
    }
