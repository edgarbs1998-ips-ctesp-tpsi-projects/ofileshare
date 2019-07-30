<?php

if ( !isset ( $_SESSION [ "browse" ] [ "viewType" ] ) ) {
	$_SESSION [ "browse" ] [ "viewType" ] = "fileManagerIcon";
}

$viewType = trim ( $_REQUEST [ "viewType" ] );
if ( in_array ( $viewType, array ( "fileManagerIcon", "fileManagerList" ) ) ) {
	$_SESSION [ "browse" ] [ "viewType" ] = $viewType;
}
