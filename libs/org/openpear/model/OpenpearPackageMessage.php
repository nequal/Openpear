<?php
import('org.rhaco.storage.db.Dao');

/**
 * Package Message
 *
 * @var serial $id
 * @var integer $package_id @{"require":true}
 * @var text $description @{"require":true}
 * @var boolean $unread
 * @var choice $type @{"choices":["maintainer","public"]}
 * @var timestamp $created
 */
class OpenpearPackageMessage extends Dao
{
    protected $id;
    protected $package_id;
    protected $description;
    protected $unread;
    protected $type;
    protected $created;
    
    protected function __init__(){
        $this->unread = true;
        $this->created = time();
        $this->type = 'maintainer';
    }
    protected function __fm_description__(){
        return HatenaSyntax::render($this->description());
    }
}
