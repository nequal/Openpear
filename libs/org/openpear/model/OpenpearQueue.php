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
    static protected $__type__ = 'type=choice(build,mail),require=true';
    static protected $__data__ = 'type=text';
    static protected $__locked__ = 'type=timestamp';
    static protected $__created__ = 'type=timestamp';
    static protected $__updated__ = 'type=timestamp';

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
        $this->created = $this->updated = time();
    }
    protected function __before_save__() {
        $this->updated = time();
    }
    protected function __fm_data__() {
        return unserialize($this->data);
    }
}
