<?php
/**
 * OpenpearAPI
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */

class OpenpearAPI extends Openpear
{
    function maintainers(){
        $c = new Criteria();
        if($this->isVariable('q')){
            $c = new Criteria(Q::ilike(Maintainer::columnName(), $this->getVariable('q'), 'p'));
        }
        $maintainers = $this->dbUtil->select(new Maintainer(), $c);
        foreach($maintainers as $maintainer){
            echo $maintainer->name . "\n";
        }
        Rhaco::end();
    }
}