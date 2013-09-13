<?php
/**
 * Pecker Lexer
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * The source of this document, reference to PHP-Parser.
 * 
 * @copyright       Pecker Scanner http://www.cnxct.com
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          CFC4N <cfc4n@cnxct.com>
 * @package         Lexer
 * @version         $Id: Lexer.php 7 2013-09-13 03:29:53Z cfc4n $
 */

class Pecker_Lexer
{
    protected $code;
    protected $tokens;
    protected $pos;
    protected $line;
    protected $errMsg;
    protected $dropTokens;

    public function __construct() {

        $this->tokenMap = $this->createTokenMap();
        // map of tokens to drop while lexing (the map is only used for isset lookup,
        // that's why the value is simply set to 1; the value is never actually used.)
        $this->dropTokens = array_fill_keys(array(T_WHITESPACE, T_OPEN_TAG), 1);
    }

    /**
     * Initializes the lexer for lexing the provided source code.
     *
     * @param string $code The source code to lex
     *
     * @throws PHPParser_Error on lexing errors (unterminated comment or unexpected character)
     */
    public function startLexing($code)
    {
        if (preg_match('/<\?(php)?\s*@Zend;[\r\n|\n]+\d+;/', $code)) {
            $this->errMsg = 'Encrypt with Zend optimizer.';
            return false;
        }
        $this->resetErrors();
        $this->tokens = token_get_all($code);
        $this->code = $code;
        $this->pos  = -1;
        $this->line =  1;
        return $this->checkError();
    }

    protected function resetErrors() {
        // clear error_get_last() by forcing an undefined variable error
        @$undefinedVariable;
    }

    protected function checkError()
    {
        $error = error_get_last();

        if (preg_match('~^Unterminated comment starting line ([0-9]+)$~',$error['message'], $matches))
        {
            $this->errMsg = 'Unterminated comment at line '.$matches[1];
            return false;
        }

        if (preg_match('~^Unexpected character in input:  \'(.)\' \(ASCII=([0-9]+)\)~s',$error['message'], $matches))
        {
            $this->errMsg = sprintf('Unexpected character "%s" (ASCII %d)', $matches[1], $matches[2]);
            return false;
        }

        // PHP cuts error message after null byte, so need special case
        if (preg_match('~^Unexpected character in input:  \'$~', $error['message']))
        {
            return false;
        }

        //@todo  对其他类型语法错误检测
        return true;
    }
    
    public function getError()
    {
        return $this->errMsg;
    }
    

    /**
     * Fetches the next token.
     *
     * @param mixed $value           Variable to store token content in
     * @param mixed $startAttributes Variable to store start attributes in
     * @param mixed $endAttributes   Variable to store end attributes in
     *
     * @return int Token id
     */
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null) {
        $startAttributes = array();
        $endAttributes   = array();
    
        while (isset($this->tokens[++$this->pos])) {
            $token = $this->tokens[$this->pos];
    
            if (is_string($token)) {
                $startAttributes['startLine'] = $this->line;
                $endAttributes['endLine']     = $this->line;
    
                // bug in token_get_all
                if ('b"' === $token) {
                    $value = 'b"';
                    return ord('"');
                } else {
                    $value = $token;
                    return ord($token);
                }
            } else {
                $this->line += substr_count($token[1], "\n");
    
                if (T_COMMENT === $token[0]) {
//                     $startAttributes['comments'][] = new PHPParser_Comment($token[1], $token[2]);
                } elseif (T_DOC_COMMENT === $token[0]) {
//                     $startAttributes['comments'][] = new PHPParser_Comment_Doc($token[1], $token[2]);
                } elseif (!isset($this->dropTokens[$token[0]])) {
                    $value = $token[1];
                    $startAttributes['startLine'] = $token[2];
                    $endAttributes['endLine']     = $this->line;
    
                    return $this->tokenMap[$token[0]];
                }
            }
        }
    
        $startAttributes['startLine'] = $this->line;
    
        // 0 is the EOF token
        return 0;
    }
    
    /**
     * Creates the token map.
     *
     * The token map maps the PHP internal token identifiers
     * to the identifiers used by the Parser. Additionally it
     * maps T_OPEN_TAG_WITH_ECHO to T_ECHO and T_CLOSE_TAG to ';'.
     *
     * @return array The token map
     */
    protected function createTokenMap() {
        $tokenMap = array();
    
        // 256 is the minimum possible token number, as everything below
        // it is an ASCII value
        for ($i = 256; $i < 1000; ++$i) {
            // T_DOUBLE_COLON is equivalent to T_PAAMAYIM_NEKUDOTAYIM
            if (T_DOUBLE_COLON === $i) {
                $tokenMap[$i] = Pecker_Parser::T_PAAMAYIM_NEKUDOTAYIM;
                // T_OPEN_TAG_WITH_ECHO with dropped T_OPEN_TAG results in T_ECHO
            } elseif(T_OPEN_TAG_WITH_ECHO === $i) {
                $tokenMap[$i] = Pecker_Parser::T_ECHO;
                // T_CLOSE_TAG is equivalent to ';'
            } elseif(T_CLOSE_TAG === $i) {
                $tokenMap[$i] = ord(';');
                // and the others can be mapped directly
            } elseif ('UNKNOWN' !== ($name = token_name($i)) && defined($name = 'Pecker_Parser::' . $name) )
            {
                $tokenMap[$i] = constant($name);
            }
        }
    
        return $tokenMap;
    }
    
    public function getTokens()
    {
        return $this->tokens;
    }
}
?>