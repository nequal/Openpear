<?php
/**
 * Templateモジュールでワンタイムチケット
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 */
class HtmlOneTimeTicketFilter{
	/**
	 * Flowのモジュール
	 * @param Flow $flow
	 */
	public function before_flow_handle(Flow $flow){
		if(!$flow->is_post()){
			$flow->vars("_onetimeticket",uniqid("").mt_rand());
			$flow->sessions("_onetimeticket",$flow->in_vars("_onetimeticket"));
		}
	}
	/**
	 * Flowのモジュール
	 * @param Flow $flow
	 */
	public function flow_verify(Flow $flow){
		if(!$flow->is_sessions("_onetimeticket") || $flow->in_vars("_onetimeticket") !== $flow->in_sessions("_onetimeticket")){
			$flow->vars("_onetimeticket",$flow->in_sessions("_onetimeticket"));
            return false;
		}
        return true;
	}
	/**
	 * Templateのモジュール
	 * @param string $src
	 * @param Tempalte $template
	 */
	public function before_template(&$src,Template $template){
		if(Tag::setof($tag,$src,"body")){
			foreach($tag->in("form") as $f){
				if(strtolower($f->in_param("method")) == "post"){
					$f->value("<input type=\"hidden\" name=\"_onetimeticket\" value=\"{\$_onetimeticket}\" />".$f->value());
					$src = str_replace($f->plain(),$f->get(),$src);
				}
			}
		}
	}
}
