<?php
/**
 * REPOS="$1"
 * REV="$2"
 * LOG=`svnlook log -r $REV "$REPOS"`
 * php /path/to/commit_hook.php "$REPOS" "$REV" "$LOG"
 */

if($argc === 4){
    require dirname(dirname(__FILE__)). '/__settings__.php';
    import('org.openpear.flow.parts.Openpear');
    
    list(, $path, $revision, $message) = $argv;
    try {
        OpenpearChangeset::commit_hook($path, $revision, $message);
    } catch (Exception $e){
        echo $e->getMessage();
    }
}
