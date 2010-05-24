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
    protected function fm_path() {
        list(,, $path) = explode('/', $this->path, 3);
        return $path;
    }
}
