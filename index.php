<?php
/**
 * Pecker Index
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
 * @package         demo
 * @version         $Id: index.php 1 2013-09-12 03:45:27Z cfc4n $
 */

require dirname(__FILE__) . '/Pecker/Autoloader.php';
Pecker_Autoloader::register();    //register autoloader

$config = array(
    'scandir' => dirname(__FILE__),
    'extend' => array('php','inc','php5'),
    'function' => array('exec','system','reate_function','passthru','shell_exec','proc_open','popen','curl_exec','parse_ini_file','show_source'),
);

try {
    $scaner = new Pecker_Scanner();
    $scaner->setPath($config['scandir']);    // set directory to scan
    $scaner->setExtend($config['extend']);
    $scaner->setFunction($config['function']);
    $scaner->run();
    $result = $scaner->getReport();

    //result of demo for show
    foreach ($result as $k => $v)
    {
        if ($v['parser'] === false)
        {
            echo $k,' ',$v['message'];
        }
        else 
        {
            if (count($v['function']) > 0)
            {
                foreach ($v['function'] as $func => $line)
                {
                    echo $k,' found function "',$func, '" in line ',implode(', ', $line),".\n";
                }
            }
        }
    }
}
catch (Exception $e)
{
    print_r($e);
}