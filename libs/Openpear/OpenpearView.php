<?php

class OpenpearView extends Openpear
{
    /**
     * トップページ
     * 
     * ## テンプレートにセットする値
     * # 'primary_tags' => primary がセットされているタグリスト(上限付き)
     * # 'recent_releases' => 最新 OpenpearRelease モデルの配列
     */
    public function index(){
        $this->vars('primary_tags', OpenpearPackageTag::getActiveCategories(16));
        $this->vars('recent_releases', C(OpenpearRelease)->find_page(null, new Paginator(20, 1), '-id'));
        return $this;
    }
    
    public function search(){
        switch($this->inVars('search_for', 'packages')){
            case 'maintainers':
                Http::redirect(url('maintainers'). '?q='. $this->inVars('q'));
            case 'packages':
            default:
                Http::redirect(url('packages'). '?q='. $this->inVars('q'));
        }
        Http::redirect(url());
    }
    
    /**
     * ダッシュボード
     * @todo
     */
    public function dashboard(){
        $this->_login_required();
        $this->vars('maintainer', $this->user());
        $this->vars('my_package_charges', C(OpenpearCharge)->find_all(Q::eq('maintainer_id', $this->user()->id())));
        $this->vars('timelines', C(OpenpearTimeline)->find_all());
        $this->vars('my_favorites', C(OpenpearFavorite)->find_all(Q::eq('maintainer_id', $this->user()->id())));
        return $this;
    }
}
