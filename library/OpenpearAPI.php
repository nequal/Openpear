<?php
/**
 * OpenpearAPI
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('tag.feed.Rss20');
Rhaco::import('model.RepositoryLog');
Rhaco::import('OpenpearFormatter');

class OpenpearAPI extends Openpear
{
    /**
     * 新規登録されたパッケージRSS
     */
    function feedNewPackage(){
        $packages = $this->dbUtil->select(new Package(), new C(
            Q::orderDesc(Package::columnCreated()),
            Q::pager(20)
        ));
        $this->_opPackageFeed($packages,
            '新規パッケージ',
            'openpear に新しく登録されたパッケージ'
        );
    }
    /**
     * 更新があったパッケージ
     */
    function feedUpdatePackage(){
        $packages = $this->dbUtil->select(new Package(), new C(
            Q::orderDesc(Package::columnUpdated()),
            Q::pager(20)
        ));
        $this->_opPackageFeed($packages,
            '更新パッケージ',
            'リリースされたり、パッケージ情報が更新されたパッケージ'
        );
    }
    function _opPackageFeed($packages, $title, $desc){
        $rss20 = new Rss20();
        $rss20->setChannel(
            $title,
            $desc,
            Rhaco::url('package'),
            'ja'
        );
        $rss20->channel->setImage($title, Rhaco::templateurl('images/header_logo.gif'), Rhaco::url('package'));
        foreach($packages as $p){
            $item = new RssItem20($p->name,
                OpenpearFormatter::d($p->description), Rhaco::url('package/'. $p->name));
            $item->setPubDate($p->updated);
            $rss20->setItem($item);
        }
        $rss20->output();
        Rhaco::end();
    }

    function feedRepository(){
        $repLogs = $this->dbUtil->select(new RepositoryLog(), new C(
            Q::orderDesc(RepositoryLog::columnRevision()),
            Q::pager(20)
        ));
        $this->_opRepositoryFeed($repLogs,
            'commits log',
            'commits log'
        );
    }
    function _opRepositoryFeed($repLogs, $title, $desc){
        $rss20 = new Rss20();
        $rss20->setChannel(
            $title,
            $desc,
            Rhaco::url('repository/'),
            'ja'
        );
        $rss20->channel->setImage($title, Rhaco::templateurl('images/header_logo.gif'), Rhaco::url('repository/'));
        foreach($repLogs as $l){
            $item = new RssItem20(sprintf('revision %s', $l->revision),
                OpenpearFormatter::d($l->log), Rhaco::url('changeset/'. $l->revision));
            $item->setPubDate($l->date);
            $item->setAuthor($l->author);
            $rss20->setItem($item);
        }
        $rss20->output();
        Rhaco::end();
    }

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
