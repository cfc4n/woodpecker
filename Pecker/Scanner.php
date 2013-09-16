<?php
/**
 * Pecker Scanner
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Pecker Scanner http://www.cnxct.com
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          CFC4N <cfc4n@cnxct.com>
 * @package         Scanner
 * @version         $Id: Scanner.php 12 2013-09-16 07:16:06Z cfc4n $
 */
class Pecker_Scanner
{
    private $extend = array();
    private $parser;
    private $report;
    private $function;
    private $path;
    private $dropdir = array();
    
    function __construct()
    {
        $this->parser = new Pecker_Parser(new Pecker_Lexer());
        $this->report = new Pecker_Loger();
        $this->extend['php'] = true;
    }
    
    /**
     * set expansion name
     * @param array $extend
     */
    public function setExtend(array $extend)
    {
        foreach ($extend as $v)
        {
            $this->extend[trim(trim($v),'.')] = true;
        }
        if (!isset($this->extend['php']))
        {
            $this->extend['php'] = true;
        }
    }
    
    /**
     * set directory path
     * @param unknown $path
     * @throws Exception
     */
    public function setPath($path)
    {
        if (substr($path,-1) == '/' || substr($path,-1) == '\\')
        {
            $path = substr($path, 0.-1);
        }
        if (!is_dir($path))
        {
            throw new Exception($path.' is not existing directory.');
        }
        $this->path = $path;
    }
    
    /**
     * set functions of check list
     * @param array $function
     */
    public function setFunction(array $function)
    {
        foreach ($function as $fun)
        {
            $this->function[trim($fun)] = true;
        }
        if(!isset($this->function['eval']))
        {
            $this->function['eval'] = true;
        }
    }
    
    /**
     * main function
     */
    public function run ()
    {
        $this->scanDir($this->path);
    }
    
    /**
     * scan directorys
     * @param string $dir
     * @throws Exception
     */
    private function scanDir ($dir)
    {
        if(($handle = opendir($dir))!== false) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..')
                {
                    if (is_dir($dir.DIRECTORY_SEPARATOR.$file))
                    {
                        if (!in_array($dir.DIRECTORY_SEPARATOR.$file,$this->dropdir))
                        {
                            $this->scanDir($dir.DIRECTORY_SEPARATOR.$file);
                        }
                    }
                    elseif (is_file($dir.DIRECTORY_SEPARATOR.$file) && $file != '.svn')
                    {
                        $arrFileinfo = pathinfo($dir.DIRECTORY_SEPARATOR.$file);
                        if (isset($arrFileinfo['extension']) && isset($this->extend[$arrFileinfo['extension']]))
                        {
                            if (!in_array($dir.DIRECTORY_SEPARATOR.$file,$this->dropdir))
                            {
                                $this->scanFile($dir.DIRECTORY_SEPARATOR.$file);
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }
        else
        {
            throw new Exception('Can\'t to read this dir '.$dir);
        }
    }
    
    /**
     * scan files
     * @param string $file
     * @return boolean
     */
    private function scanFile($file)
    {
        $this->report->setFile($file);
        $bRS = $this->parser->parse(file_get_contents($file));
        if(false === $bRS)
        {
            $this->report->errorLog($this->parser->getErrmsg());
            return false;
        }
        $this->checkTokens($this->parser->getTokens());
    }
    
    /**
     * check dangerous functions
     * @param array $tokens
     */
    private function checkTokens(array $tokens)
    {
        $i = 0;
        foreach ($tokens as $k => $token)
        {
            if (is_array($token))
            {
                switch ($token[0])
                {
                    case T_EVAL:
                        $this->report->catchLog($token[1],$token[2],$this->parser->getPieceTokenAll($k));
                        break;
                    case T_FUNCTION:
                        if (isset($this->function[$token[1]]))
                        {
                            $this->report->catchLog($token[1],$token[2],$this->parser->getPieceTokenAll($k));
                        }
                        break;
                    case T_VARIABLE:
                        $ntoken = $this->parser->getNextToken($k);
                        $ptoken = $this->parser->getPreToken($k);
                        if ($ntoken === '(' && $ptoken != '->' && $ptoken !== '::' && $ptoken !== 'function' && $ptoken !== 'new')
                        {
                            $this->report->catchLog($token[1], $token[2],$this->parser->getPieceTokenAll($k));
                        }
                        break;
                    case T_STRING:
                        if (isset($this->function[$token[1]]))
                        {
                            $ntoken = $this->parser->getNextToken($k);
                            $ptoken = $this->parser->getPreToken($k);
                            if ($ntoken === '(' && $ptoken != '->' && $ptoken != '::' && $ptoken != 'function')
                            {
                                $this->report->catchLog($token[1], $token[2],$this->parser->getPieceTokenAll($k));
                            }
                        }
                        break;
                    case T_INCLUDE:
                    case T_INCLUDE_ONCE:
                    case T_REQUIRE:
                    case T_REQUIRE_ONCE:
                        if (isset($this->function[$token[1]]))
                        {
                            $infile = $this->parser->getFilepathToken($k);
                            $fileinfo = pathinfo($infile);
                            if (!isset($this->extend[$fileinfo['extension']]))
                            {
                                $this->report->catchLog($token[1], $token[2],$this->parser->getPieceTokenAll($k));
                            }
                        }
                        break;
                    default:
                }
            }
        }
    }
    
    /**
     * get results
     * @return Ambigous <multitype:, multitype:boolean string multitype: >
     */
    public function getReport()
    {
        return $this->report->getReport();
    }
}

?>