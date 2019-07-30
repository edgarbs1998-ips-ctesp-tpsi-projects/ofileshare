<?php

class TemplateFunctions {
    static function outputSuccess ( ) {
        $html = "";
        $html .= "<script>\n";
        $html .= "$(document).ready(function() {\n";
        $success = Notification::getSuccess ( );
        if ( count ( $success ) ) {
            $htmlArr = array( );
            foreach ( $success as $success ) {
                $htmlArr [ ] = $success;
            }

            $msg = implode ( "<br/>", $htmlArr );
        }
        $html .= "showSuccessNotification('Success', '" . str_replace ( "'", "", $msg ) . "');\n";
        $html .= "});\n";
        $html .= "</script>\n";

        return $html;
    }

    static function outputErrors ( ) {
        $html = "";
        $html .= "<script>\n";
        $html .= "$(document).ready(function() {\n";
        $errors = notification::getErrors();
        if ( count ( $errors ) ) {
            $htmlArr = array ( );
            foreach ( $errors AS $error ) {
                $htmlArr [ ] = $error;
            }

            $msg = implode ( "<br/>", $htmlArr );
        }
        $html .= "showErrorNotification('Error', '" . str_replace ( "'", "", $msg ) . "');\n";
        $html .= "});\n";
        $html .= "</script>\n";

        return $html;
    }
}
