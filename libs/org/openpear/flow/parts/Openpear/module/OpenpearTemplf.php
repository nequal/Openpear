<?php
module('model.OpenpearMaintainer');

class OpenpearTemplf
{
	private $user;
	
	protected function __new__($user){
		$this->user = $user;
	}
    final public function isme(OpenpearMaintainer $maintainer){
        if($this->user instanceof OpenpearMaintainer && $this->user->id() == $maintainer->id()){
            return true;
        }
        return false;
    }
    final public function str($a){
        return str($a);
    }
    final public function svn_diff($diff) {
        $result = '';
        foreach (array_map('trim', explode("\n", $diff)) as $line) {
            $line = htmlspecialchars($line, ENT_QUOTES);
            if (preg_match('/^Index: (.+?)$/', $line, $match)) {
                if (!empty($result)) $result .= '</pre>'. "\n";
                $result .= sprintf('<h4>%s</h4>', $match[1]);
            } else if (preg_match('/^\=+/', $line)){
                $result .= '<pre class="diff">';
            } else if (preg_match('/^(\+{3}|\-{3}) /', $line)) {
                # skip
            } else if (preg_match('/^@@/', $line)) {
                $result .= sprintf('<span class="meta">%s</span>', $line);
                $result .= "\n";
            } else if (preg_match('/^\+/', $line)) {
                $result .= sprintf('<span class="plus">%s</span>', $line);
                $result .= "\n";
            } else if (preg_match('/^\-/', $line)) {
                $result .= sprintf('<span class="minus">%s</span>', $line);
                $result .= "\n";
            } else {
                $result .= $line;
                if (!empty($line)) $result .= "\n";
            }
        }
        return $result;
    }
    final public function svn_log_msg($revision){
        $log = Subversion::cmd('log', array(module_const('svn_root')), array('revision' => $revision));
        return (string)$log[0]['msg'];
    }
    final public function date_ago($date, $from=null){
        $from = is_null($from)? time(): $from;
        $diff = intval($from - strtotime($date));
        if($diff < 0){
            // future is not yet
            // $future = true;
            return $date;
        }
        $diff = abs($diff);
        $days = intval($diff / (60*60*24));
        $hours = intval(($diff % (60*60*24)) / (60*60));
        $minutes = intval((($diff % (60*60*24)) % (60*60)) / 60);
        $times = intval((($diff % (60*60*24)) % (60*60)) % 60);
        $sameDay = (date('Y/m/d') == date('Y/m/d', strtotime($date)));
        
        if($hours < 1 && $minutes < 1 && $days == 0){
            return sprintf(' %d seconds ago', $times);
        } else if($hours < 1 && $days == 0){
            return sprintf(' %d minutes ago', $minutes);
        } else if($hours >= 1 && $hours < 6 && $days == 0){
            return sprintf(' %d hours ago', $hours);
        } else if($hours > 6 && $days == 0 && $sameDay){
            return ' today';
        } else if($hours > 6 && $days < 2 &&!$sameDay){
            return ' yesterday';
        } else if($days == 2){
            return ' 2 days ago';
        } else if($days == 3){
            return ' 3 days ago';
        } else {
            return $date;
        }
    }
    final public function strtotime($str){
        return strtotime($str);
    }
    final public function srcpath_link(OpenpearPackage $package, $path){
        $ret = '';
        $parent = '';
        foreach(explode('/', $path) as $p){
            $link = File::absolute(url(sprintf('package/%s/src/%s', $package->name())), implode('/', array($parent, $p)));
            $ret .= sprintf('<a href="%s">%s</a>', $link, $p);
            $parent .= $p;
        }
        return $ret;
    }
    final public function tlicon($type){
        switch($type){
            case 'release':
                return Template::base_media_url(). '/images/global-icon-star.png';
            case 'changeset':
                return Template::base_media_url(). '/images/global-icon-checked.png';
            case 'user_activities':
                return Template::base_media_url(). '/images/global-icon-user.png';
            case 'package_setting':
                return Template::base_media_url(). '/images/global-icon-gear.png';
            case 'favorite':
                return Template::base_media_url(). '/images/global-icon-star.png';
        }
    }
    final public function tlalt($type){
        switch($type){
            case 'release':
                return 'Release:';
            case 'changeset':
                return 'Changeset:';
            case 'user_activities':
                return 'Setting:';
            case 'package_setting':
                return 'Setting:';
            case 'favorite':
                return 'Fav:';
        }
    }
    
    final public function count(array $array){
        return count($array);
    }
    final public function hash($key='rand'){
        $key = ($key === 'rand')? mt_rand(0, 99999): $key;
        return sha1(md5($key));
    }
    final public function d($v){
        Log::info('########## debug ##########');
        Log::d($v);
    }
}
