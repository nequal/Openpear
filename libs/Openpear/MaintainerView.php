<?php
class MaintainerView extends Openpear
{
    /**
     * プロフィール
     */
    public function model($maintainer_name){
        try {
            $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $maintainer_name));
        } catch(Exception $e){
            return $this->_not_found();
        }
        $this->vars('object', $maintainer);
        $this->vars('charges', C(OpenpearCharge)->find_all(Q::eq('maintainer_id', $maintainer->id())));
        $this->vars('favorites', C(OpenpearFavorite)->find_all(Q::eq('maintainer_id', $maintainer->id())));
        $this->vars('timelines', C(OpenpearTimeline)->find_all(new Paginator(10), Q::eq('maintainer_id', $maintainer->id()), Q::order('-id')));
        return $this;
    }
    /**
     * メンテナ検索
     */
    public function models(){
        $paginator = new Paginator(20, $this->inVars('page', 1));
        $this->vars('object_list', C(OpenpearMaintainer)->find_page($this->inVars('q'), $paginator), 'name');
        $this->vars('paginator', $paginator->add(array('q' => $this->inVars('q'))));
        return $this;
    }
}