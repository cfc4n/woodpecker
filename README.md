Pecker Scanner
======

A scanner named pecker, written in php,It can check dangerous functions with lexical analysis.

Use:
=====
Config:
```php
    $config = array(
        'scandir' => dirname(__FILE__),
        'extend' => array('php','inc','php5'),
        'function' => array('exec','system','create_function','passthru','shell_exec','proc_open','popen','curl_exec','parse_ini_file','show_source','include','preg_replace'),
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
Array
(
    [Pecker\test\1.php] => Array
        (
            [parser] => 1
            [message] => 
            [function] => Array
                (
                    [eval] => Array
                        (
                            [0] => Array
                                (
                                    [line] => 23
                                    [code] => (       //get it
gzinflate    ( $str   ($str1)))
                                )

                            [1] => Array
                                (
                                    [line] => 35
                                    [code] => ('$str = time();')
                                )

                        )

                    [exec] => Array
                        (
                            [0] => Array
                                (
                                    [line] => 25
                                    [code] => ('dir')
                                )

                            [1] => Array
                                (
                                    [line] => 36
                                    [code] => ('dir')
                                )

                        )

                )

        )

    [Pecker\test\111.php] => Array
        (
            [parser] => 1
            [message] => 
            [function] => Array
                (
                )

        )

    [Pecker\test\3.php] => Array
        (
            [parser] => 1
            [message] => 
            [function] => Array
                (
                )

        )

)

```

Info
=====
+ Home Page:[http://www.cnxct.com/pecker-scanner/][1]
+ WeiBo:[http://weibo.com/n/CFC4N][2]

Reference
=====
+ [PHPPHP][3]
+ [PHP-Parser][4]

Other
=====
+ [Pecker Scanner Server][5]

[1]:http://www.cnxct.com/pecker-scanner/
[2]:http://weibo.com/n/CFC4N
[3]:https://github.com/ircmaxell/PHPPHP
[4]:https://github.com/nikic/PHP-Parser
[5]:https://github.com/cfc4n/pecker-server
