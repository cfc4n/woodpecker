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
 * @version         $Id: index.php 9 2013-09-13 07:54:47Z cfc4n $
 */

require dirname(__FILE__) . '/Pecker/Autoloader.php';
Pecker_Autoloader::register();    //register autoloader

$config = array(
    'scandir' => dirname(__FILE__).DIRECTORY_SEPARATOR.'test',
    'extend' => array('php','inc','php5'),
    'function' => array('exec','system','create_function','passthru','shell_exec','proc_open','popen','curl_exec','parse_ini_file','show_source'),
);

try {
    $scaner = new Pecker_Scanner();
    $scaner->setPath($config['scandir']);    // set directory to scan
    $scaner->setExtend($config['extend']);
    $scaner->setFunction($config['function']);
    $scaner->run();
    $result = $scaner->getReport();


    $html = '';
    //result of demo for show
    foreach ($result as $k => $v)
    {
        if ($v['parser'] === false)
        {
            $html .= '<tr><td title="'.$k.'">'.str_replace($config['scandir'], '', $k).'</td> <td align="center"> - </td> <td align="center"> - </td> <td class="focus">'.$v['message'].'</td></tr>';
        }
        else 
        {
            $n = count($v['function']);
            if ( $n > 0)
            {
                $rowspan = false;
                foreach ($v['function'] as $func => $line)
                {
                    if (!$rowspan)
                    {
                        $html .='<tr><td rowspan="'.$n.'" title="'.$k.'">'.str_replace($config['scandir'], '', $k).'</td>';
                        $rowspan = true;
                    }
                    else 
                    {
                        $html .='<tr>';
                    }
                    $html1 = '';
                    foreach ($line as $c)
                    {
                        $html1 .= 'line '.$c['line'].' :'.'<span class="code" title="'.$func.' '.htmlspecialchars($c['code']).'">'.$func.' '.htmlspecialchars(substr($c['code'],0,50)).'</span><br/>';
                    }
                    $html .='<td>'.$func.'</td> <td>'.$html1.'</td> <td align="center"> - </td></tr>';
                }
            }
        }
    }
    if ($html == '')
    {
        $html = '<tr><td colspan="4" align="center">Congratulations,It is very safe...</td></tr>';
    }
    $report = file_get_contents('template.html');
    $report = str_replace('{PATH}', '<span class="string">'.$config['scandir'].'</span>', $report);
    $report = str_replace('{EXTEND}', '<span class="string">'.implode('</span>,<span class="string">',$config['extend']).'</span>', $report);
    $report = str_replace('{FUNCTION}','<span class="string">'.implode('</span>,<span class="string"> ',$config['function']).'</span>', $report);
    $report = str_replace('{RESULT}', $html, $report);
    $filename = 'report_'.date('YmdHis').'.html';
    file_put_contents($filename, $report);
    echo '<a href="'.$filename.'">Completed,View report.</a>';
}
catch (Exception $e)
{
    print_r($e);
}