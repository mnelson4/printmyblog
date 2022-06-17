<?php

namespace Twine\helpers;

/**
 *
 * Class EEH_HTML
 *
 * Sometimes when writing PHP you need to generate some standard HTML,
 * but either not enough to warrant creating a template file,
 * or the amount of PHP conditionals and/or loops peppered throughout the HTML
 * just make it really ugly and difficult to read.
 * This class simply adds a bunch of methods for generating basic HTML tags.
 * Most of the methods have the same name as the HTML tag they generate, and most have the same set of parameters.
 *
 * @package         Event Espresso
 * @subpackage    core
 * @author              Brent Christensen
 *
 *
 */
class Html
{
    /**
     * @var Html
     */
    protected static $instance;

    /**
     * @var int[]
     */
    protected $indent;

    /**
     * @return Html
     */
    public static function instance()
    {
        if (! self::$instance instanceof Html) {
            self::$instance = new Html();
        }
        return self::$instance;
    }

    /**
     * Resets indentation
     * @return Html
     */
    public static function reset()
    {
        self::$instance = null;
        return self::instance();
    }

    /**
     *
     * @access    private
     */
    protected function __construct()
    {
        // set some initial formatting for table indentation
        $this->indent = array(
            'none' => 0,
            'form' => 0,
            'radio' => 0,
            'checkbox' => 0,
            'select' => 0,
            'option' => 0,
            'optgroup' => 0,
            'table' => 1,
            'thead' => 2,
            'tbody' => 2,
            'tr' => 3,
            'th' => 4,
            'td' => 4,
            'div' => 0,
            'h1' => 0,
            'h2' => 0,
            'h3' => 0,
            'h4' => 0,
            'h5' => 0,
            'h6' => 0,
            'p' => 0,
            'ul' => 0,
            'li' => 1,
        );
    }

    /**
     * Generates an opening HTML <XX> tag and adds any passed attributes
     * if passed content, it will also add that, as well as the closing </XX> tag
     *
     * @access protected
     * @param string $tag
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @param bool $force_close
     * @return string
     */
    protected function aTag(
        $tag = 'div',
        $content = '',
        $id = '',
        $class = '',
        $style = '',
        $other_attributes = '',
        $force_close = false
    ) {
        $attributes = ! empty($id) ? ' id="' . $this->sanitizeId($id) . '"' : '';
        $attributes .= ! empty($class) ? ' class="' . $class . '"' : '';
        $attributes .= ! empty($style) ? ' style="' . $style . '"' : '';
        $attributes .= ! empty($other_attributes) ? ' ' . $other_attributes : '';
        $html = $this->nl(0, $tag) . '<' . $tag . $attributes . '>';
        $html .= ! empty($content) ? $this->nl(1, $tag) . $content : '';
        $indent = ! empty($content) || $force_close ? true : false;
        $html .= ! empty($content) || $force_close ? $this->closeTag($tag, $id, $class, $indent) : '';
        return $html;
    }

    /**
     * Returns an opening HTML tag.
     * @param string $tag
     * @param string $id
     * @param string $class
     * @param string $style
     * @param string $other_attributes
     * @return string
     */
    public function openTag(
        $tag = 'div',
        $id = '',
        $class = '',
        $style = '',
        $other_attributes = ''
    ) {
        return $this->aTag(
            $tag,
            '',
            $id,
            $class,
            $style,
            $other_attributes,
            false
        );
    }

    /**
     * @param string $tag
     * @param string $content
     * @param string $id
     * @param string $class
     * @param string $style
     * @param string $other_attributes
     *
     * @return string
     */
    public function tag(
        $tag = 'div',
        $content = '',
        $id = '',
        $class = '',
        $style = '',
        $other_attributes = ''
    ) {
        return $this->aTag(
            $tag,
            $content,
            $id,
            $class,
            $style,
            $other_attributes,
            true
        );
    }


    /**
     * Generates HTML closing </XX> tag - if passed the id or class attribute
     * used for the opening tag, will append a comment
     *
     * @param string $tag
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param bool $indent
     * @return string
     */
    public function closeTag($tag = 'div', $id = '', $class = '', $indent = true)
    {
        $comment = '';
        if ($id) {
            $comment = $this->comment('close ' . $id) . $this->nl(0, $tag);
        } elseif ($class) {
            $comment = $this->comment('close ' . $class) . $this->nl(0, $tag);
        }
        $html = $indent ? $this->nl(-1, $tag) : '';
        $html .= '</' . $tag . '>' . $comment;
        return $html;
    }


    /**
     *  Generates HTML opening <div> tag and adds any passed attributes
     *  to add an id use:       echo $this->div( 'this is some content', 'footer' );
     *  to add a class use:     echo $this->div( 'this is some content', '', 'float_left' );
     *  to add a both an id and a class use:    echo $this->div( 'this is some content', 'footer', 'float_left' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function div($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('div', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates HTML closing </div> tag - if passed the id or class attribute used for the opening div tag, will
     * append a comment
     * usage: echo $this->divx();
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function divx($id = '', $class = '')
    {
        return $this->closeTag('div', $id, $class);
    }


    /**
     * Generates HTML <h1></h1> tags, inserts content, and adds any passed attributes
     * usage: echo $this->h1( 'This is a Heading' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function h1($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('h1', $content, $id, $class, $style, $other_attributes, true);
    }


    /**
     * Generates HTML <h2></h2> tags, inserts content, and adds any passed attributes
     * usage: echo $this->h2( 'This is a Heading' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function h2($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('h2', $content, $id, $class, $style, $other_attributes, true);
    }


    /**
     * Generates HTML <h3></h3> tags, inserts content, and adds any passed attributes
     * usage: echo $this->h3( 'This is a Heading' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function h3($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('h3', $content, $id, $class, $style, $other_attributes, true);
    }


    /**
     * Generates HTML <h4></h4> tags, inserts content, and adds any passed attributes
     * usage: echo $this->h4( 'This is a Heading' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function h4($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('h4', $content, $id, $class, $style, $other_attributes, true);
    }


    /**
     * Generates HTML <h5></h5> tags, inserts content, and adds any passed attributes
     * usage: echo $this->h5( 'This is a Heading' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function h5($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('h5', $content, $id, $class, $style, $other_attributes, true);
    }


    /**
     * Generates HTML <h6></h6> tags, inserts content, and adds any passed attributes
     * usage: echo $this->h6( 'This is a Heading' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function h6($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('h6', $content, $id, $class, $style, $other_attributes, true);
    }


    /**
     * Generates HTML <p></p> tags, inserts content, and adds any passed attributes
     * usage: echo $this->p( 'this is a paragraph' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function p($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('p', $content, $id, $class, $style, $other_attributes, true);
    }


    /**
     *  Generates HTML opening <ul> tag and adds any passed attributes
     *  usage:      echo $this->ul( 'my-list-id', 'my-list-class' );
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function ul($id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('ul', '', $id, $class, $style, $other_attributes);
    }


    /**
     * Generates HTML closing </ul> tag - if passed the id or class attribute used for the opening ul tag, will append
     * a comment
     * usage: echo $this->ulx();
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function ulx($id = '', $class = '')
    {
        return $this->closeTag('ul', $id, $class);
    }


    /**
     * Generates HTML <li> tag, inserts content, and adds any passed attributes
     * if passed content, it will also add that, as well as the closing </li> tag
     * usage: echo $this->li( 'this is a line item' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function li($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('li', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates HTML closing </li> tag - if passed the id or class attribute used for the opening ul tag, will append
     * a comment
     * usage: echo $this->lix();
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function lix($id = '', $class = '')
    {
        return $this->closeTag('li', $id, $class);
    }


    /**
     *    Generates an HTML <table> tag and adds any passed attributes
     *    usage: echo $this->table();
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function table($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('table', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates an HTML </table> tag - if passed the id or class attribute used for the opening ul tag, will
     * append a comment
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function tablex($id = '', $class = '')
    {
        return $this->closeTag('table', $id, $class);
    }


    /**
     *    Generates an HTML <thead> tag and adds any passed attributes
     *    usage: echo $this->thead();
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function thead($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('thead', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates an HTML </thead> tag - if passed the id or class attribute used for the opening ul tag, will
     * append a comment
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function theadx($id = '', $class = '')
    {
        return $this->closeTag('thead', $id, $class);
    }


    /**
     *    Generates an HTML <tbody> tag and adds any passed attributes
     *    usage: echo $this->tbody();
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function tbody($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('tbody', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates an HTML </tbody> tag - if passed the id or class attribute used for the opening ul tag, will
     * append a comment
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function tbodyx($id = '', $class = '')
    {
        return $this->closeTag('tbody', $id, $class);
    }


    /**
     *    Generates an HTML <tr> tag and adds any passed attributes
     *    usage: echo $this->tr();
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function tr($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('tr', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates an HTML </tr> tag - if passed the id or class attribute used for the opening ul tag, will append
     * a comment
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function trx($id = '', $class = '')
    {
        return $this->closeTag('tr', $id, $class);
    }


    /**
     *    Generates an HTML <th> tag and adds any passed attributes
     *    usage: echo $this->th();
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function th($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('th', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates an HTML </th> tag - if passed the id or class attribute used for the opening ul tag, will
     * append a comment
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function thx($id = '', $class = '')
    {
        return $this->closeTag('th', $id, $class);
    }


    /**
     *    Generates an HTML <td> tag and adds any passed attributes
     *    usage: echo $this->td();
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function td($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('td', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates an HTML </td> tag - if passed the id or class attribute used for the opening ul tag, will
     * append a comment
     *
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @return string
     */
    public function tdx($id = '', $class = '')
    {
        return $this->closeTag('td', $id, $class);
    }


    /**
     * For generating a "hidden" table row, good for embedding tables within tables
     * generates a new table row with one td cell that spans however many columns you set
     * removes all styles from the tr and td
     *
     * @param string $content
     * @param int $colspan
     * @return string
     */
    public function noRow($content = '', $colspan = 2)
    {
        return $this->tr(
            $this->td($content, '', '', 'padding:0; border:none;', 'colspan="' . $colspan . '"'),
            '',
            '',
            'padding:0; border:none;'
        );
    }


    /**
     * Generates HTML <label></label> tags, inserts content, and adds any passed attributes
     * usage: echo $this->span( 'this is some inline text' );
     *
     * @access public
     * @param string $href URL to link to
     * @param string $link_text - the text that will become "hyperlinked"
     * @param string $title - html title attribute
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function link(
        $href = '',
        $link_text = '',
        $title = '',
        $id = '',
        $class = '',
        $style = '',
        $other_attributes = ''
    ) {
        $link_text = ! empty($link_text) ? $link_text : $href;
        $attributes = ! empty($href) ? ' href="' . $href . '"' : '';
        $attributes .= ! empty($id) ? ' id="' . $this->sanitizeId($id) . '"' : '';
        $attributes .= ! empty($class) ? ' class="' . $class . '"' : '';
        $attributes .= ! empty($style) ? ' style="' . $style . '"' : '';
        $attributes .= ! empty($title) ? ' title="' . esc_attr($title) . '"' : '';
        $attributes .= ! empty($other_attributes) ? ' ' . $other_attributes : '';
        return "<a{$attributes}>{$link_text}</a>";
    }


    /**
     *    Generates an HTML <img> tag and adds any passed attributes
     *    usage: echo $this->img();
     *
     * @param string $src - html src attribute ie: the path or URL to the image
     * @param string $alt - html alt attribute
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function img($src = '', $alt = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        $attributes = ! empty($src) ? ' src="' . esc_url_raw($src) . '"' : '';
        $attributes .= ! empty($alt) ? ' alt="' . esc_attr($alt) . '"' : '';
        $attributes .= ! empty($id) ? ' id="' . $this->sanitizeId($id) . '"' : '';
        $attributes .= ! empty($class) ? ' class="' . $class . '"' : '';
        $attributes .= ! empty($style) ? ' style="' . $style . '"' : '';
        $attributes .= ! empty($other_attributes) ? ' ' . $other_attributes : '';
        return '<img' . $attributes . '/>';
    }


    /**
     * Generates HTML <label></label> tags, inserts content, and adds any passed attributes
     * usage: echo $this->span( 'this is some inline text' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function label($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('label', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates HTML <span></span> tags, inserts content, and adds any passed attributes
     * usage: echo $this->span( 'this is some inline text' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function span($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('span', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates HTML <span></span> tags, inserts content, and adds any passed attributes
     * usage: echo $this->span( 'this is some inline text' );
     *
     * @param string $content - inserted after opening tag, and appends closing tag, otherwise tag is left open
     * @param string $id - html id attribute
     * @param string $class - html class attribute
     * @param string $style - html style attribute for applying inline styles
     * @param string $other_attributes - additional attributes like "colspan", inline JS, "rel" tags, etc
     * @return string
     */
    public function strong($content = '', $id = '', $class = '', $style = '', $other_attributes = '')
    {
        return $this->aTag('strong', $content, $id, $class, $style, $other_attributes);
    }


    /**
     * Generates an html <--  comment --> tag
     *  usage: echo comment( 'this is a comment' );
     *
     * @param string $comment
     * @return string
     */
    public function comment($comment = '')
    {
        return ! empty($comment) ? $this->nl() . '<!-- ' . $comment . ' -->' : '';
    }


    /**
     * Generates a line break
     *
     * @param int $nmbr - the number of line breaks to return
     * @return string
     */
    public function br($nmbr = 1)
    {
        return str_repeat('<br />', $nmbr);
    }


    /**
     * Generates non-breaking space entities based on number supplied
     *
     * @param int $nmbr - the number of non-breaking spaces to return
     * @return string
     */
    public function nbsp($nmbr = 1)
    {
        return str_repeat('&nbsp;', $nmbr);
    }


    /**
     * Functionally does the same as the wp_core function sanitize_key except it does NOT use
     * strtolower and allows capitals.
     *
     * @param string $id
     * @return string
     */
    public function sanitizeId($id = '')
    {
        $key = str_replace(' ', '-', trim($id));
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $key);
    }


    /**
     * Return a newline and tabs ("nl" stands for "new line")
     *
     * @param int $indent the number of tabs to ADD to the current indent (can be negative or zero)
     * @param string $tag
     * @return string - newline character plus # of indents passed (can be + or -)
     */
    public function nl($indent = 0, $tag = 'none')
    {
        $html = "\n";
        $this->indent($indent, $tag);
        for ($x = 0; $x < $this->indent[$tag]; $x++) {
            $html .= "\t";
        }
        return $html;
    }


    /**
     * Changes the indents used in $this->nl. Often its convenient to change
     * the indentation level without actually creating a new line
     *
     * @param int $indent can be negative to decrease the indentation level
     * @param string $tag
     */
    public function indent($indent, $tag = 'none')
    {
        if (! isset($this->indent[$tag])) {
            $this->indent[$tag] = 0;
        }
        $this->indent[$tag] += (int)$indent;
        $this->indent[$tag] = $this->indent[$tag] >= 0 ? $this->indent[$tag] : 0;
    }
}
