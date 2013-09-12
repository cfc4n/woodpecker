pecker
======

A scanner named pecker, written in php,It can check dangerous functions with lexical analysis.

Use:
=====
Config:
```php
    $config = array(
        'scandir' => dirname(__FILE__),
        'extend' => array('php','inc','php5'),
        'function' => array('exec','system','reate_function','passthru','shell_exec','proc_open','popen','curl_exec','parse_ini_file','show_source'),
    );
```

Main:
```php
    $scaner = new Pecker_Scanner();
    $scaner->setPath($config['scandir']);    // set directory to scan
    $scaner->setExtend($config['extend']);
    $scaner->setFunction($config['function']);
    $scaner->run();
    $result = $scaner->getReport();
```

Result:
```php
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
```

Info
=====
Home:http://www.cnxct.com/pecker-scanner/
Blog:http://www.cnxct.com/
WeiBo:http://weibo.com/n/CFC4N

Reference
=====
PHPPHP:https://github.com/ircmaxell/PHPPHP
PHP-Parser:https://github.com/nikic/PHP-Parser.


