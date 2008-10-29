<?php
/**
 * HatenaSyntax.php
 *
 * PHP version 5.2.5
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    anatoo <study.anatoo@gmail.com> 
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://d.hatena.ne.jp/anatoo/
 */

class HatenaSyntax
{
  protected $firstCharSyntaxes = array();
  protected $inlineSyntaxes = array();
  protected $footnoteSyntax;
  protected $markupSyntaxes = array();
  protected $openingTags = array();
  protected $closingTags = array();
  protected $options;
  
  public function __construct($options = array())
  {
    if(!is_array($options)) throw new Exception('argument is not array.');
    
    $this->options = $options;
    
    // 汎用文法の追加
    // 汎用といってもDefaultとBlockquoteはちょっと特殊です
    // 理由はgetFirstCharSyntaxIdentifierを参照
    $this->addFirstCharSyntax(new HatenaSyntax_Head($this->getOption('headlevel', 3)))
         ->addFirstCharSyntax(new HatenaSyntax_Table())
         ->addFirstCharSyntax(new HatenaSyntax_DefinitionList())
         ->addFirstCharSyntax(new HatenaSyntax_Default())
         ->addFirstCharSyntax(new HatenaSyntax_Blockquote(false))
         ->addFirstCharSyntax(new HatenaSyntax_Blockquote(true))
         ->addFirstCharSyntax(new HatenaSyntax_List())
         ->addFirstCharSyntax(new HatenaSyntax_List(true));
    
    $this->addMarkupSyntax(new HatenaSyntax_Pre(false, $this->getOption('htmlescape', false)))
         ->addMarkupSyntax(new HatenaSyntax_Pre(true));
    
    $this->addInlineSyntax(new HatenaSyntax_Link());
    
    // 非汎用文法
    // applyInlineSyntaxでついでにこの文法も適用される。
    $this->footnoteSyntax = new HatenaSyntax_Footnote($this->getOption('id', ''));
  }
  protected function getOption($key, $default = false)
  {
    if(!isset($this->options[$key])) return $default;
    return $this->options[$key];
  }
  public function addMarkupSyntax(HatenaSyntax_MarkupSyntaxInterface $syntax)
  {
    $this->markupSyntaxes[$syntax->getOpeningIdentifier()] = $syntax;
    $this->openingTags[$syntax->getOpeningIdentifier()] = true;
    $this->closingTags[$syntax->getClosingIdentifier()] = true;
    
    return $this;
  }
  public function addFirstCharSyntax(HatenaSyntax_FirstCharSyntaxInterface $syntax)
  {
    $this->firstCharSyntaxes[$syntax->getIdentifier()] = $syntax;
    return $this;
  }
  public function addInlineSyntax(HatenaSyntax_InlineSyntaxInterface $syntax)
  {
    $this->inlineSyntaxes[] = $syntax;
    return $this;
  }
  public function parseStructure($contents)
  {
    $openingTags = $this->openingTags;
    $closingTags = $this->closingTags;
    $markupSyntaxes = $this->markupSyntaxes;
    $firstCharSyntaxes = $this->firstCharSyntaxes;
    
    $lines = $this->getLines($contents);
    $result = array();
    for($i = 0, $length = count($lines); $i < $length; $i++) {
      $block = array('lines' => array());
      
      // ブロック文法の種類の確認
      // MarkupSyntaxだったら
      if(isset($openingTags[$lines[$i]])) {
        $block['syntax'] = $markupSyntaxes[$lines[$i]];
        $block['type'] = 'markup';
        
        for($i++; $i < $length; $i++) {
          if(isset($closingTags[$lines[$i]])) break;
          $block['lines'][] = $lines[$i];
        }
      }
      // FirstCharSyntaxだったら
      else {
        $firstChar = $this->getFirstCharSyntaxIdentifier($lines[$i]);
        
        $block['syntax'] = $firstCharSyntaxes[$firstChar];
        $block['type'] = 'firstchar';
        
        for(; $i < $length; $i++) {
          if($firstChar !== $this->getFirstCharSyntaxIdentifier($lines[$i])) break;
          $block['lines'][] = $lines[$i];
        }
        $i--;
      }
      
      $result[] = $block;
    }
    
    return $result;
  }
  public function parse($contents) {
    $structure = $this->parseStructure($contents);
    $result = array();
    $htmlEscape = $this->getOption('htmlescape');
    
    $result[] = '<div class="section">' . PHP_EOL;
    foreach($structure as $block) {
      if($block['type'] === 'markup')
        foreach($block['lines'] as $line) $block['syntax']->parse($line);
      else
        foreach($block['lines'] as $line) {
          if($htmlEscape) $line = htmlspecialchars($line, ENT_QUOTES);
          $line = $this->applyInlineSyntax($line);
          $block['syntax']->parse($line);
        }
      
      $result[] = $block['syntax']->getResult();
    }
    $result[] = '</div>' . PHP_EOL . PHP_EOL;
    
    $result[] = $this->getFootnoteSyntaxResult();
    
    return implode('', $result);
  }
  public function getLines($contents)
  {
    return split("\r\n|\n|\r", $contents);
  }
  public function getFootnoteSyntaxResult()
  {
    return $this->footnoteSyntax->getResult();
  }
  public function applyInlineSyntax($line)
  {
    foreach($this->inlineSyntaxes as $syntax) {
      $line = $syntax->parse($line);
    }
    $line = $this->footnoteSyntax->parse($line);
    return $line;
  }
  protected function getFirstCharSyntaxIdentifier($line)
  {
    if($line === '<<') return '<<';
    elseif($line === '>>') return '>>';
    
    $char = substr($line, 0, 1);
    
    if(isset($this->firstCharSyntaxes[$char])) return $char;
    
    if(isset($this->openingTags[ $line])) return false;
    
    return 'default';
  }
}

/**
 * リストや見出し、表等のはてな記法を表現するためのインターフェイス
 */
interface HatenaSyntax_FirstCharSyntaxInterface
{
  public function getIdentifier();
  public function parse($line);
  public function getResult();
}

/**
 * リンクなどのはてな記法を表現するためのインターフェイス
 */
interface HatenaSyntax_InlineSyntaxInterface
{
  public function parse($line);
}

/**
 * pre記法等のためのインターフェイス
 */
interface HatenaSyntax_MarkupSyntaxInterface
{
  public function getOpeningIdentifier();
  public function getClosingIdentifier();
  public function parse($line);
  public function getResult();
}

class HatenaSyntax_Table implements HatenaSyntax_FirstCharSyntaxInterface
{
  protected $table = array();
  public function getIdentifier()
  {
    return '|';
  }
  public function parse($line)
  {
    $_ = explode('|', $line);
    array_pop($_);
    array_shift($_);
    $this->table[] = $_;
  }
  public function getResult()
  {
    $table = $this->table;
    $this->table = array();
    
    $result = '<table>' . PHP_EOL;
    foreach($table as $col) {
      $result .= '<tr>';
      foreach($col as $cell) {
        if(substr($cell, 0, 1) === '*') {
          $result .= '<th>' . substr($cell, 1) . '</th>';
        }
        else {
          $result .= '<td>' . $cell . '</td>';
        }
      }
      $result .= '</tr>' . PHP_EOL;
    }
    $result .= '</table>' . PHP_EOL;
    
    return $result;
  }
}

class HatenaSyntax_Pre implements HatenaSyntax_MarkupSyntaxInterface
{
  protected $result = '';
  protected $superPreFlag;
  protected $htmlEscape;
  public function __construct($superPreFlag = false, $htmlEscape = false)
  {
    $this->superPreFlag = !!$superPreFlag;
    $this->htmlEscape = !!$htmlEscape;
  }
  public function getOpeningIdentifier()
  {
    return $this->superPreFlag ? '>||' : '>|';
  }
  public function getClosingIdentifier()
  {
    return $this->superPreFlag ? '||<' : '|<';
  }
  public function parse($line)
  {
    $this->result .= $line .PHP_EOL;
  }
  public function getResult()
  {
    $result = $this->superPreFlag || $this->htmlEscape
              ? htmlspecialchars($this->result, ENT_QUOTES)
              : $this->result;
    $this->result = '';
    
    return '<pre>' . PHP_EOL . $result . '</pre>' . PHP_EOL;
  }
}

class HatenaSyntax_Footnote
{
  protected $footnotes = array();
  protected $id;
  public function __construct($id = '')
  {
    $this->id = empty($id) ? '': '_' . $id;
  }
  public function parse($line)
  {
    while(true) {
      if(($result = $this->getFootnote($line)) === false) break;
      
      $this->footnotes[] = $result[1];
      $num = count($this->footnotes);
      $id = $this->id;
      $line = $result[0] 
            . '(<a href="#f' . $num . $id . '" name ="b' . $num  . $id 
            .'" title="' . $result[1] . '">*' . $num . '</a>)' . $result[2] . PHP_EOL;
    }
    
    return $line;
  }
  public function getFootnote($line)
  {
    if(($_ = $this->seekFootnote($line)) === false) return false;
    
    $left = substr($line, 0, $_[0]);
    $contents = substr($line, $_[0] + 2, $_[1] - $_[0] - 2);
    $right = substr($line, $_[1] + 2);
    
    return array($left, $contents, $right);
  }
  public function seekFootnote($line)
  {
    $opening = strpos($line, '((');
    if($opening === false) return false;
    
    $closing = strpos($line, '))', $opening);
    if($closing === false) return false;
    
    return array($opening, $closing);
  }
  public function getResult()
  {
    $footnotes = $this->footnotes;
    $this->footnotes = array();
    $result = '<div class="footnote">' . PHP_EOL;
    $id = $this->id;
    
    foreach($footnotes as $num => $note)
      $result .= '<p><a href="#b' . ++$num  . $id . '" name="f' . $num  . $id . '">*' . $num 
              . '</a>: ' . $note . '</p>' .  PHP_EOL;
    
    $result .= '</div>' . PHP_EOL;
    
    return $result;
  }
}

class HatenaSyntax_Link implements HatenaSyntax_InlineSyntaxInterface
{
  public function parse($line)
  {
    return
      preg_replace_callback('/\[(https?:\/\/[^:]+)(:title=([^\]]*))?\]/', 
      create_function('$m', '
        return "<a href=\"{$m[1]}\">" 
              . (isset($m[3]) ? $m[3] : $m[1] )
              . "</a>";
      '),
      $line);
  }
}

class HatenaSyntax_List implements HatenaSyntax_FirstCharSyntaxInterface
{
  protected $list = array();
  protected $identifier;
  public function __construct($orderedFlag = false)
  {
    $this->identifier = $orderedFlag ? '+' : '-';
  }
  public function getIdentifier()
  {
    return $this->identifier;
  }
  public function parse($line)
  {
    $level = $this->countLevel($line);
    $list =& $this->list;
    
    for($i = 0; $i < $level; $i++) {
      if(count($list) > 0) {
        if(!is_array($list[count($list) - 1])) $list[] = array();
        $list =& $list[count($list) - 1];
      }
      else {
        $list =& $list[];
      }
    }
    
    $list[] = substr($line, $level);
  }
  
  /**
   * $lineの先頭の+,-の深さを数える
   */
  public function countLevel($line)
  {
    $level = 0;
    $len = strlen($line);
    for($i = 0; $i < $len; $i++) {
      if($line[$i] === '-' || $line[$i] === '+') $level++;
      else break;
    }
    
    return $level - 1;
  }
  
  public function getResult()
  {
    $result = $this->buildList($this->list);
    $this->list = array();
    
    return $result;
  }
  
  /**
   * 配列からリストを表現するタグを生成
   */
  public function buildList($arr)
  {
    if(count($arr) > 0 && !is_array($arr[0])) $tagName = (substr($arr[0], 0, 1) === '+') ? 'ol': 'ul';
    else $tagName = $this->identifier === '+' ? 'ol': 'ul';
    
    $result = '<' . $tagName . '>' . PHP_EOL;
    foreach($arr as $item) {
      if(is_array($item)) $result .= $this->buildList($item);
      else {
        $item = substr($item, 1);
        $result .= '<li>' . $item . '</li>' . PHP_EOL;
      }
    }
    $result .= '</' . $tagName . '>' . PHP_EOL;
    return $result;
  }
}

class HatenaSyntax_Head implements HatenaSyntax_FirstCharSyntaxInterface
{
  protected $result = '';
  protected $baseLevel;
  public function __construct($baseLevel = 3)
  {
    $baseLevel = (int)$baseLevel;
    if($baseLevel <= 0) throw new Exception("invalid argument: {$baseLevel}");
    $this->baseLevel = $baseLevel;
  }
  public function getIdentifier()
  {
    return '*';
  }
  public function parse($line)
  {
    $base = $this->baseLevel;
    $level = $this->countLevel($line);
    
    $this->result .= '<h' . ($base + $level - 1) . '>' 
                  . substr($line, $level) 
                  . '</h' . ($base + $level - 1) . '>' 
                  . PHP_EOL;
  }
  /**
   * $lineの先頭の*の数を数える
   */
  public function countLevel($line)
  {
    $level = 0;
    $len = strlen($line);
    for($i = 0; $i < $len || $i < 3; $i++) {
      if($line[$i] === '*') $level++;
      else break;
    }
    
    return $level;
  }
  public function getResult()
  {
    $result = $this->result;
    $this->result = '';
    return $result;
  }
}

class HatenaSyntax_Blockquote implements HatenaSyntax_FirstCharSyntaxInterface
{
  protected $opening;
  public function __construct($opening)
  {
    $this->opening = !!$opening;
  }
  public function getIdentifier()
  {
    return $this->opening ? '>>': '<<';
  }
  public function parse($line)
  {}
  public function getResult()
  {
    return ($this->opening ? '<blockquote>': '</blockquote>') . PHP_EOL;
  }
}

class HatenaSyntax_DefinitionList implements HatenaSyntax_FirstCharSyntaxInterface
{
  protected $items;
  public function getIdentifier()
  {
    return ':';
  }
  public function parse($line)
  {
    $this->items[] = explode(':', substr($line, 1), 2);
  }
  public function getResult()
  {
    $result = '<dl>' . PHP_EOL;
    foreach($this->items as $item) {
      $result .= $item[0] === '' ? '' :'<dt>' . $item[0] . '</dt>' . PHP_EOL;
      if(isset($item[1])) $result .= '<dd>' . $item[1] . '</dd>' . PHP_EOL;
    }
    $this->items = array();
    $result .= '</dl>' . PHP_EOL;
    
    return $result;
  }
}

class HatenaSyntax_Default implements HatenaSyntax_FirstCharSyntaxInterface
{
  protected $result = '';
  protected $newLineFlag = false;
  public function getIdentifier()
  {
    return 'default';
  }
  public function parse($line)
  {
    if($line === '') {
      /**
       * 何もない行が二つ続いたら改行(<br/>)を挿入
       */
      if($this->newLineFlag) {
        $this->result .= '<br/>' . PHP_EOL;
        $this->newLineFlag = false;
      }
      else {
        $this->newLineFlag = true;
      }
      
      $this->result .= PHP_EOL;
    }
    else {
      $this->result .= '<p>' . $line . '</p>' . PHP_EOL;
      $this->newLineFlag = false;
    }
  }
  public function getResult()
  {
    $result = $this->result;
    $this->result = '';
    $this->newLineFlag = false;
    
    return $result;
  }
}