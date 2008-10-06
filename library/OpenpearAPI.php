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
    
    function toggleFavorite(){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $r = array('error' => 1, 'message' => 'unknown error');
        if($this->isPost()){
            // maintainer fav とかも欲しくなるかなあ？
            if($this->isVariable('packageId')){
                $fav = $this->dbUtil->get(new Favorite(), new C(Q::eq(Favorite::columnPackage(), $this->getVariable('packageId')), Q::eq(Favorite::columnMaintainer(), $u->id)));
                if(Variable::istype('Favorite', $fav)){
                    $r = $this->dbUtil->delete($fav) ? 
                        array('error' => 0, 'message' => 'Deleted...') : array('error' => 1, 'message' => 'Err!!');
                } else {
                    $fav = new Favorite();
                    $fav->setPackage($this->getVariable('packageId'));
                    $fav->setMaintainer($u->id);
                    if($fav->save($this->dbUtil)){
                        $r = array('error' => 0, 'message' => 'Fav!!');
                    }
                }
            }
        }
        if($this->isVariable('type') && $this->getVariable('type') == 'json') $this->json($r);
        Header::redirect(Rhaco::url('packages'));
    }
}
