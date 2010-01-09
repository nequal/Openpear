<?php

function openpear_autoload_handler($class_name){
    $modules = array('Account', 'Document', 'Maintainer',
        'Message', 'Openpear', 'Package', 'Release', 'Source', 'Timeline');
    if(in_array($class_name, $modules)){
        import('org.openpear.'. $class_name);
    }
    $models = array(
        // models
        'OpenpearMaintainer' => 'Maintainer.model.OpenpearMaintainer',
        'OpenpearOpenidMaintainer' => 'Maintainer.model.OpenpearOpenidMaintainer',
        'OpenpearMessage' => 'Message.model.OpenpearMessage',
        'OpenpearCharge' => 'Package.model.OpenpearCharge',
        'OpenpearFavorite' => 'Package.model.OpenpearFavorite',
        'OpenpearNewprojectQueue' => 'Package.model.OpenpearNewprojectQueue',
        'OpenpearPackage' => 'Package.model.OpenpearPackage',
        'OpenpearPackageMessage' => 'Package.model.OpenpearPackageMessage',
        'OpenpearPackageTag' => 'Package.model.OpenpearPackageTag',
        'OpenpearTag' => 'Package.model.OpenpearTag',
        'OpenpearRelease' => 'Release.model.OpenpearRelease',
        'OpenpearReleaseQueue' => 'Release.model.OpenpearReleaseQueue',
        'OpenpearChangeset' => 'Source.model.OpenpearChangeset',
        'OpenpearTimeline' => 'Timeline.model.OpenpearTimeline',
        // others
        'OpenpearFlow' => 'Openpear.OpenpearFlow',
        'OpenpearTemplf' => 'Openpear.OpenpearTemplf',
        'OpenpearAccountModule' => 'Openpear.module.OpenpearAccountModule',
        'OpenpearException' => 'Openpear.exception.OpenpearException',
    );
    if(isset($models[$class_name])){
        import('org.openpear.'. $models[$class_name]);
    }
}

spl_autoload_register('openpear_autoload_handler');
