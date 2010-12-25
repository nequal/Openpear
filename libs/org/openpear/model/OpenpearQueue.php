<?php
import('org.rhaco.storage.db.Dao');

/**
 * Queue
 *
 * @var serial $id
 * @var choice $type @{"require":true,"choices":["build","upload_release","mail"]}
 * @var text $data
 * @var timestamp $locked
 * @var timestamp $created
 * @var timestamp $updated
 */
class OpenpearQueue extends Dao
{
    protected $id;
    protected $type;
    protected $data;
    protected $locked;
    protected $created;
    protected $updated;

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
    protected function __before_save__($commit) {
        $this->updated = time();
    }
    protected function __fm_data__() {
        return unserialize($this->data);
    }
}
