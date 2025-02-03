<?php

use Illuminate\Support\Facades\File;

if (! function_exists('get_installed_composer')) {
    function get_installed_composer()
    {

        if (isset($GLOBALS['_composer_bin_dir'])) {
            $installedPath = realpath(concatenate_path($GLOBALS['_composer_bin_dir'], '../composer/installed.php'));
        } else {
            $installedPath = base_path('vendor/composer/installed.php');
        }

        $installed = require $installedPath;

        return $installed;
    }
}

if (! function_exists('get_package_installed_version')) {
    function get_package_installed_version($package)
    {
        $installedComposer = get_installed_composer();

        return $installedComposer['versions'][$package]
            ? $installedComposer['versions'][$package]['pretty_version']
            : null;
    }
}

if (! function_exists('is_modularity_development')) {
    function is_modularity_development()
    {
        return get_installed_composer()['root']['name'] === 'unusualify/modularity-dev';
    }
}

if (! function_exists('is_modularity_production')) {
    function is_modularity_production()
    {
        return ! is_modularity_development();
    }
}

if (! function_exists('get_modularity_vendor_dir')) {
    function get_modularity_vendor_dir($dir = null)
    {
        $vendor_dir = modularityConfig('vendor_dir');

        if ($dir) {
            $vendor_dir = concatenate_path($vendor_dir, $dir);
        }

        return $vendor_dir;
    }
}

if (! function_exists('get_modularity_vendor_path')) {
    function get_modularity_vendor_path($dir = null)
    {
        $vendor_dir = get_modularity_vendor_dir();

        if ($dir) {
            $vendor_dir = concatenate_path($vendor_dir, $dir);
        }

        return base_path($vendor_dir);
    }
}

if (! function_exists('get_modularity_src_path')) {
    function get_modularity_src_path($dir = null)
    {
        $vendor_dir = get_modularity_vendor_path('src');

        if ($dir) {
            $vendor_dir = concatenate_path($vendor_dir, $dir);
        }

        return base_path($vendor_dir);
    }
}

if (! function_exists('get_package_version')) {
    function get_package_version($package = null)
    {
        $tag = trim(shell_exec('cd ' . base_path() . ' && git describe --tags --abbrev=0'));

        if ($package) {
            if (is_modularity_development() && $package === 'unusualify/modularity') {
                return 'development';
            }

            return get_package_installed_version($package);
        }

        return $tag;
    }
}

if (! function_exists('set_env_file')) {
    function set_env_file($variable, $value)
    {
        // Read the current .env file
        $envFile = base_path('.env');
        $envContents = File::get($envFile);

        // Replace the APP_VERSION line
        $envContents = preg_replace(
            '/' . $variable . '=.*/',
            $variable . '=' . $value,
            $envContents
        );

        // Write the modified contents back to .env
        File::put($envFile, $envContents);

    }
}
