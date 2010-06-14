<?php
$__pear_config_file = __DIR__. '/channel.config.php';
if (file_exists($__pear_config_file)) {
    $__pear_config = @include($__pear_config_file);
    def('org.openpear.config.OpenpearConfig@pear_domain', $__pear_config['server']['domain']);
    def('org.openpear.config.OpenpearConfig@pear_alias', $__pear_config['server']['alias']);
}

require_once 'HatenaSyntax.php';
