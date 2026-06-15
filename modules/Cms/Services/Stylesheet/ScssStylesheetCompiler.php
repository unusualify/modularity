<?php

namespace Modules\Cms\Services\Stylesheet;

/**
 * Optional SCSS → CSS using scssphp when package is installed and {@see modularousConfig('cms_stylesheets.scssphp.enabled')}.
 */
final class ScssStylesheetCompiler
{
    public function compileIfEnabled(?string $scss): string
    {
        if ($scss === null || trim($scss) === '') {
            return '';
        }

        if (! (bool) modularousConfig('cms_stylesheets.scssphp.enabled', false)) {
            return '';
        }

        if (! class_exists(\ScssPhp\ScssPhp\Compiler::class)) {
            return '';
        }

        try {
            $compiler = new \ScssPhp\ScssPhp\Compiler;

            if (method_exists($compiler, 'compileString')) {
                return (string) $compiler->compileString($scss)->getCss();
            }

            return (string) $compiler->compile($scss);
        } catch (\Throwable) {
            return '';
        }
    }
}
