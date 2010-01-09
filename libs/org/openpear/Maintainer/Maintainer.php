<?php

class Maintainer extends OpenpearFlow
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
        $paginator = new Paginator(20, $this->in_vars('page', 1));
        $this->vars('object_list', C(OpenpearMaintainer)->find_page($this->in_vars('q'), $paginator), 'name');
        $this->vars('paginator', $paginator->add(array('q' => $this->in_vars('q'))));
        return $this;
    }
    public function update_json(){
        if(!$this->is_login()){
            return $this->json_response(array('status' => 'ng', 'error' => 'required sign-in'));
        }
        try {
            if(!$this->is_post()) throw new OpenpearException('request method is unsupported');
            $maintainer = $this->user();
            $maintainer->set_vars($this->vars());
            $maintainer->save(true);
            Exceptions::validation();
        } catch(Exception $e){
            return $this->json_response(array('status' => 'ng', 'error' => $e->getMessage()));
        }
        return $this->json_response(array('status' => 'ok'));
    }
}