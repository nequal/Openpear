<?php
import('org.rhaco.storage.db.Dao');

class OpenpearQueue extends Dao
{
    protected $id;
    protected $type;
    protected $data;
    protected $locked;
    protected $created;
    protected $updated;
    static protected $__id__ = 'type=serial';
    static protected $__type__ = 'type=choice(build,upload_release,mail),require=true';
    static protected $__data__ = 'type=text';
    static protected $__locked__ = 'type=timestamp';
    static protected $__created__ = 'type=timestamp';
    static protected $__updated__ = 'type=timestamp';

    static public function fetch_queues($type, $limit=5) {
        return C(OpenpearQueue)->find_all(
            new Paginator($limit),
            Q::lt('locked', time()),
            Q::eq('type', $type),
            Q::order('updated')
        );
    }

    /**
     * タスクの実行開始
     * @param int $lock
     * @return void
     **/
    public function start($lock = 3600) {
        $this->locked = time() + $lock;
        $this->save(true);
    }

    protected function __init__() {
        $this->created = $this->updated = $this->locked = time();
    }
    protected function __before_save__() {
        $this->updated = time();
    }
    protected function __fm_data__() {
        return unserialize($this->data);
    }
}
