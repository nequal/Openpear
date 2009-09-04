<?php
/**
 * php /path/to/commit_hook.php "$REPOS" "$REV" "$MES"
 */
if($argc === 3){
    require dirname(dirname(__FILE__)). '/__settings__.php';
    import('Openpear');
    
    list($path, $revision, $message) = $argv;
    try {
        OpenpearChangeset::commit_hook($path, $revision, $message);
    } catch (Exception $e){
        echo $e->getMessage();
    }
}
