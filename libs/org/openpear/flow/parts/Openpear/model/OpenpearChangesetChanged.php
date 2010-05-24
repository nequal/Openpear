<?php
class OpenpearChangesetChanged extends Object
{
    protected $status;
    protected $type;
    protected $path;
    static protected $__status__ = 'type=choice(add,modified,remove)';
    
    protected function __set_status__($status){
        switch($status){
            case 'A ':
                $this->status = 'add';
                break;
            case 'D ':
                $this->status = 'remove';
                break;
            case 'U ':
            case ' U':
            case 'UU':
                $this->status = 'modified';
                break;
            default:
                $this->status = $status;
        }
    }
    protected function __fm_path__() {
        list(, $path) = explode('/', $this->path, 2);
        return $path;
    }
}
