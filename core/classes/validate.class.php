<?php

class Validate {
    static function validateUsername ( $username ) {
        if ( strlen ( $username ) < 6 || strlen ( $username ) > 64 ) {
            return "Your username must be between 6 and 64 characters";
        }

        if ( !preg_match ( '/^[a-zA-Z0-9_]+$/', $username ) ) {
            return "Your username can only contain alphanumeric characters and underscores";
        }

        return true;
    }

    static function validateEmail ( $email ) {
        if ( !filter_var ( $email, FILTER_VALIDATE_EMAIL ) ) {
            return "Your email address is invalid";
        }
        
        $domain = explode ( "@", $email );
        $domain = array_pop ( $domain );
        if ( !checkdnsrr ( $domain, "MX" ) ) {
            return "Your email address does not exist";
        }

        return true;
    }

    static function validatePassword ( $password ) {
        if ( strlen ( $password ) < 8 || strlen ( $password ) > 72 ) {
            return "Your password must be between 8 and 72 characters";
        }

        if ( preg_match_all ( '/[a-z]/', $password ) < 1 ) {
            return "Your password must contain at least one lowercase character";
        }

        if ( preg_match_all ( '/[A-Z]/', $password ) < 1 ) {
            return "Your password must contain at least one uppsercase character";
        }

        if ( preg_match_all ( '/[0-9]/', $password ) < 1 ) {
            return "Your password must contain at least one numeric character";
        }

        return true;
    }

    static function prepareOutput ( $input, $allowedChars = null, $length = null ) {
        if ($allowedChars != null ) {
			$input = self::removeInvalidCharacters ( $input, $allowedChars );
		}

		if ( $length != null ) {
			if ( strlen ( $input ) > $length ) {
				$input = substr ( $input, 0, $length - 3 ) . "...";
			}
		}
        
        $input = htmlspecialchars ( $input, ENT_QUOTES, "UTF-8" );

        return $input;
    }

    static function removeInvalidCharacters ( $input, $allowedChars = "abcdefghijklmnopqrstuvwxyz 1234567890" ) {
		$str = "";
		for ( $i = 0; $i < strlen ( $input ); ++$i ) {
			if ( !stristr ( $allowedChars, $input [ $i ] ) ) {
				continue;
			}

			$str .= $input [ $i ];
		}

		return $str;
	}

    static function validDate ( $date, $format = "Y-m-d H:i:s" ) {
		$d = DateTime::createFromFormat ( $format, $date );

		return $d && $d->format ( $format ) == $date;
	}
}