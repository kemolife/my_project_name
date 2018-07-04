<?php

namespace SingAppBundle\Helper;


class PHPFunctionsHelper
{
    public static function array_keys_exist( $keys, array $array  ) {
        $count = 0;
        if ( ! is_array( $keys ) ) {
            $keys = func_get_args();
            array_shift( $keys );
        }
        foreach ( $keys as $key ) {
            if ( isset( $array[$key] ) || array_key_exists( $key, $array ) ) {
                $count ++;
            }
        }

        return count( $keys ) === $count;
    }
}