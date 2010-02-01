<?php
import('org.rhaco.storage.db.Dao');
module('model.OpenpearMessage');
class OpenpearMaintainer extends Dao
{
    protected $id;
    protected $name;
    protected $mail;
    protected $fullname;
    protected $profile;
    protected $url;
    protected $location;
    protected $password;
    protected $svn_password;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__name__ = 'type=alnum,unique=true,require=true';
    static protected $__mail__ = 'type=email,require=true';
    static protected $__fullname__ = 'type=string';
    static protected $__profile__ = 'type=text';
    static protected $__url__ = 'type=string';
    static protected $__location__ = 'type=string';
    static protected $__password__ = 'type=string,require=true';
    static protected $__svn_password__ = 'type=string';
    static protected $__created__ = 'type=timestamp';
    
    protected $new_password;
    protected $new_password_conf;
    static protected $__new_password__ = 'extra=true';
    static protected $__new_password_conf__ = 'extra=true';
    
    protected function __init__(){
        $this->created = time();
    }
    
    /**
     * 文字列表現
     */
    protected function __str__(){
        return empty($this->fullname)? $this->name(): $this->fullname();
    }
    /**
     * 作成/更新前処理
     */
    protected function __before_save__(){
        if($this->new_password() && $this->new_password() === $this->new_password_conf()){
            $this->password = sha1($this->new_password());
            $this->svn_password = crypt($this->new_password());
        }
    }
    protected function __after_save__(){
        $template = new Template();
        $template->vars('maintainers', C(OpenpearMaintainer)->find_all());
        File::write(module_const('svn_passwd_file'), $template->read('files/passwd.txt'));
    }
    
    /**
     * 新規作成時検証
     */
    protected function __create_verify__(){
        if(!$this->new_password()){
            Exceptions::add(new Exception('Subversion Password is required'), 'new_password');
        }
        if(!$this->new_password_conf() || $this->new_password() !== $this->new_password_conf()){
            Exceptions::add(new Exception('Incorrect Confirm Password'), 'new_password_conf');
        }
    }
    
    /**
     * 作成後処理
     * - Subversion アカウントの作成
     * - メールの送信
     */
    protected function __after_create__(){
        $registered_message = new Template();
        $registered_message->vars('maintainer', $this);
        
        $message = new OpenpearMessage();
        $message->maintainer_to_id($this->id());
        $message->subject('Welcome to Openpear!');
        $message->description($registered_message->read('messages/registered.txt'));
        $message->type('system');
        $message->save();
        
        $message = new OpenpearMessage();
        $message->maintainer_to_id($this->id());
        $message->subject('Please join Google Groups');
        $message->description('Google Groups にまだ登録してないみたいなんですけど，登録してくださいよ．それとももうしました？');
        $message->type('system_notice');
        $message->mail(false);
        $message->save();
    }
    
    protected function __verify_url__(){
        if(!empty($this->url) && !preg_match('/s?https?:\/\/[\-_\.!~*\'\(\)a-zA-Z0-9;\/\?:@&=\+$,%#]+/i', $this->url)){
            return false;
        }
        return true;
    }
    
    /**
     * 正しいパスワードか認証する
     * 過去のパスワードはひどいので適宜修正
     */
    public function certify($password){
        if($this->is_password()){
            if($this->password() === sha1($password)) return true;
            return false;
        }
        $org_password = $this->svn_password();
        $salt = substr($org_password, 0, 2);
        if($org_password === crypt($password, $salt)){
            $this->password = sha1($password);
            $this->save(true);
            return true;
        }
        return false;
    }
}
