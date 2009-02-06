<?php
/**
 * StaticView
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('view.ViewBase');

class StaticView extends ViewBase
{
    function page($name){
        $name = preg_replace('/\.+/', '.', $name);
        $template = Rhaco::filepath('static', $name. '.html');
        if(is_file(Rhaco::templatepath($template))){
            return $this->parser($template);
        }
        return $this->_notFound();
    }
}