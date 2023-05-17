<?php

    function wpfpd_instance() {
        return WPFPD\Plugin::instance();
    }

    if (!function_exists('str_contains')) {
        function str_contains($haystack, $needle) {
            return $needle !== '' && mb_strpos($haystack, $needle) !== false;
        }
    }