<?php

if (!function_exists('database_exists')) {
    function database_exists() {
        try {
            \DB::connection()->getPDO();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
