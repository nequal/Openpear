<?php
import('org.rhaco.service.OpenIDAuth');
module('exception.OpenpearException');
module('model.OpenpearChangeset');
module('model.OpenpearCharge');
module('model.OpenpearFavorite');
module('model.OpenpearMaintainer');
module('model.OpenpearMessage');
module('model.OpenpearNewprojectQueue');
module('model.OpenpearOpenidMaintainer');
module('model.OpenpearPackage');
module('model.OpenpearPackageTag');
module('model.OpenpearRelease');
module('model.OpenpearReleaseQueue');
module('model.OpenpearTag');
module('model.OpenpearTimeline');

class Openpear extends Flow
{
    protected function __new__($dict=null){
        $this->dict($dict);
        parent::__new__();
        $this->m('Template')->statics('ot', 'Openpear.OpenpearTemplf');
    }
    
    protected function _login_required($redirect_to=null){
        if($this->isLogin()){
            return ;
        }
        if($redirect_to === null){
            Http::redirect(url('account/login'));
        }
        Http::redirect(url('account/login?redirect_to='. url($redirect_to)));
    }
    
    /**
     * @todo
     */
    protected function _not_found(){
        Http::status_header(404);
        exit;
    }
    
    /**
     * テンプレートだけ表示したい（要らないだろうけど，いずれなんかの情報を与えたい）
     */
    /**public function static(){
        return $this;
    }
    */
}
