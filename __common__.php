<?php
$__pear_config_file = dirname(__FILE__). '/channel.config.php';
if (file_exists($__pear_config_file)) {
    $__pear_config = @include($__pear_config_file);
    def('org.openpear.flow.parts.Openpear@pear_domain', $__pear_config['server']['domain']);
    def('org.openpear.flow.parts.Openpear@pear_alias', $__pear_config['server']['alias']);
}
