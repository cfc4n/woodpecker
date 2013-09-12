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
 * @version         $Id: Autoloader.php 1 2013-09-12 03:45:27Z cfc4n $
 */

class Pecker_Autoloader
{
    static public function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    static public function autoload($class)
    {
        if (0 !== strpos($class, 'Pecker')) {
            return;
        }
        $file = dirname(dirname(__FILE__)) . '/' . strtr($class, '_', '/') . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
}
?>