<?php

// Auto load class when it is required by the script
spl_autoload_register ( function ( $className ) {
    $classFile = CORE_ROOT . "/classes/" . lcfirst ( $className ) . ".class.php";

    if ( is_file ( $classFile ) ) {
        require_once ( $classFile );
    }
} );
