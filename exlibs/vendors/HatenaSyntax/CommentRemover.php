<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_CommentRemover
{
	static function remove($str)
	{
		$str = preg_replace_callback(
			'/<!--.*?-->|\n>\|[^|]*\|\n.*?\|\|<\n/s', 
			array(__CLASS__, 'replace'), 
			"\n" . $str . "\n");
		return substr($str, 1, -1);
	}

	static function replace($matches)
	{
		return substr($matches[0], 0, 1) === '<' ? '' : $matches[0];
	}
}
