<?php
module('command.SubversionCommand');
module('command.SubversionCat');
module('command.SubversionCopy');
module('command.SubversionDiff');
module('command.SubversionExport');
module('command.SubversionImport');
module('command.SubversionInfo');
module('command.SubversionList');
module('command.SubversionLog');
module('command.SubversionPropget');

class Subversion extends Object
{
    static public function cmd($command, $vars=array(), $options=array(), $dict=null){
        $command = 'Subversion'. ucfirst($command);
        $svn = is_null($dict)? new $command(): new $command($dict);

        foreach($vars as $key => $var) $svn->vars($key, $var);
        foreach($options as $key => $option) $svn->options($key, $option);
        return $svn->exec();
    }

    static public function look($command, $vars=array(), $options=array(), $dict=null){
    	return SubversionCommand::look($command,$vars,$options,$dict);
    }
}
