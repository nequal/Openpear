<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Locator
{
    protected $blockRef = null, $lineElementRef = null;
    protected $shared = array();
    protected $facade;
    
    private function __construct()
    {
        $this->setup();
    }

    protected function setup()
    {
        $this->lineElement;
        $this->lineElementRef = new HatenaSyntax_LineElement($this->bracket, $this->footnote, $this->inlineTag);
        $this->block;
        $this->blockRef = PEG::memo(new HatenaSyntax_Block($this));
    }

    static function it()
    {
        static $obj = null;
        return $obj ? $obj : $obj = new self;
    }
    
    function __get($name)
    {
        return isset($this->shared[$name]) ? 
            $this->shared[$name] : 
            $this->shared[$name] = $this->{'create' . $name}();
    }

    protected function nodeCreater($type, PEG_IParser $parser, Array $keys = array())
    {
        return new HatenaSyntax_NodeCreater($type, $parser, $keys);
    }
    
    protected function createLineChar()
    {
        return PEG::anything();
    }
    
    protected function createFootnote()
    {
        $elt = PEG::subtract(
            PEG::choice($this->bracket, $this->lineChar), 
            '))');
                            
        $parser = PEG::pack('((', 
                            PEG::many1($elt), 
                            '))');
                            
        return $this->nodeCreater('footnote', $parser);
    }

    protected function createInlineTag()
    {
        $parser = new HatenaSyntax_InlineTag($this->lineElement);

        return $this->nodeCreater('inlinetag', $parser, array('name', 'body'));
    }
    
    protected function createLineElement()
    {
        return PEG::ref($this->lineElementRef);
    }
    
    protected function createLineSegment()
    {
        return PEG::many($this->lineElement);
    }
    
    protected function createHttpLink()
    {
        $title_char = PEG::subtract($this->lineChar, ']');
        $title = PEG::choice(
            PEG::second(':title=', PEG::join(PEG::many1($title_char))),
            PEG::second(':title', '') 
        );
        
        $url_char = PEG::subtract($this->lineChar, ']', ':title');
        $url = PEG::join(PEG::seq(
            PEG::choice('http://', 'https://'), 
            PEG::many1($url_char))); 

        $parser = PEG::seq($url, PEG::optional($title));
        
        return $this->nodeCreater('httplink', $parser, array('href', 'title'));
    }
    
    protected function createImageLink()
    {
        $url_char = PEG::subtract($this->lineChar, ']', ':image]');
        $url = PEG::join(PEG::seq(
            PEG::choice('http://', 'https://'), 
            PEG::many1($url_char)));
                                  
        $parser = PEG::first($url, ':image');
        
        return $this->nodeCreater('imagelink', $parser);
    }
    
    protected function createKeywordLink()
    {
        $body = PEG::join(PEG::many1(PEG::subtract($this->lineChar, ']]')));
        $body = PEG::subtract($body, 'javascript:');
        $parser = PEG::pack('[', $body, ']');
        
        return $this->nodeCreater('keywordlink', $parser);
    }
    
    protected function createNullLink()
    {
        $body = PEG::join(PEG::many1(PEG::subtract($this->lineChar, '[]')));
        $parser = PEG::pack(']', $body, '[');
        
        return $parser;
    }
    
    protected function createTableOfContents()
    {
        $parser = new HatenaSyntax_Regex('/^\[:contents]$/');
        
        return $this->nodeCreater('tableofcontents', $parser);
    }
    
    protected function createInlineTableOfContents()
    {
        $parser = PEG::token(':contents');
        return $this->nodeCreater('tableofcontents', $parser);
    }
    
    protected function createBracket()
    {
        return PEG::pack('[', PEG::choice(
            $this->inlineTableOfContents, 
            $this->nullLink, 
            $this->keywordLink, 
            $this->imageLink, 
            $this->httpLink), 
        ']');
    }

    protected function createSeparator()
    {
        $parser = PEG::token('====');
        return $this->nodeCreater('separator', $parser);
    }

    protected function createDefinitionList()
    {
        $parser = new HatenaSyntax_DefinitionList($this->lineElement);
        return $this->nodeCreater('definitionlist', $parser);
    }
    
    protected function createPre()
    {
        $parser = new HatenaSyntax_Pre($this->lineElement);
        
        return $this->nodeCreater('pre', $parser);
    }
    
    protected function createSuperPre()
    {
        $parser = new HatenaSyntax_SuperPre;

        return $this->nodeCreater('superpre', $parser, array('type', 'body'));
    }
    
    protected function createHeader()
    {
        $parser = new HatenaSyntax_Header($this->lineElement);
        
        return $this->nodeCreater('header', $parser, array('level', 'name', 'body'));
    }

    protected function createNoParagraph()
    {
        $parser = new HatenaSyntax_NoParagraph($this->lineElement);

        return $this->nodeCreater('noparagraph', $parser, array('tag', 'attr', 'body'));
    }

    protected function createList()
    {
        $parser = new HatenaSyntax_List($this->lineElement);
        
        return $this->nodeCreater('list', $parser);
    }
    
    protected function createTable()
    {
        $parser = new HatenaSyntax_Table($this->lineElement);
        
        return $this->nodeCreater('table', $parser);
    }

    protected function createBlockQuote()
    {
        $parser = new HatenaSyntax_Quote($this->block);
                                      
        return $this->nodeCreater('blockquote', $parser, array('url', 'body'));
    }
    
    protected function createParagraph()
    {
        $parser = new HatenaSyntax_Paragraph($this->lineElement);
        
        return $this->nodeCreater('paragraph', $parser);
    }
    
    protected function createEmptyParagraph()
    {
        $parser = PEG::count(PEG::many1(PEG::token('')));

        return $this->nodeCreater('emptyparagraph', $parser);
    }
    
    protected function createBlock()
    {
        $parser = PEG::ref($this->blockRef);

        return $parser;
    }

    protected function createParser()
    {
        return $this->nodeCreater('root', PEG::many($this->block));
    }
    
}
