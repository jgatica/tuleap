<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload8e68e9441f58761de87f55ca726f11ba($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'admsswplugin' => '/admsswPlugin.class.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload8e68e9441f58761de87f55ca726f11ba');
// @codeCoverageIgnoreEnd