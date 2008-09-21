<?php

class OpenpearTemplateFilter
{
    function publish($src, &$parser){
        if(SimpleTag::setof($tag, $src)){
            foreach($tag->getIn('ul') as $ul){
                if($ul->param('id') == 'menu'){
                    $lists = array();
                    $f = false;
                    foreach($ul->getIn('li') as $k => $li){
                        $a = $li->getIn('a');
                        $a = $a[0];
                        if($k) $isActive = (strpos('http://'.$_SERVER['HTTP_HOST'].Rhaco::uri(), $a->param('href')) !== false) ? ' class="active"' : '';
                        else $isActive = '';
                        $lists[] = array(
                            'isActive' => $isActive,
                            'href' => $a->param('href'),
                            'caption' => $a->getValue(),
                        );
                        if($isActive) $f = true;
                    }
                    if($f == false) $lists[0]['isActive'] = ' class="active"';
                    $val = '';
                    foreach($lists as $li){
                        $val .= sprintf('<li%s><a href="%s">%s</a></li>'."\n", $li['isActive'], $li['href'], $li['caption']);
                    }
                    $src = str_replace($ul->getValue(), $val, $src);
                }
            }
        }
        return $src;
    }
}
