<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ) }}">
    <head>
        @include("{$MODULARITY_VIEW_NAMESPACE}::partials.head", [
            // 'pageTitle' => $pageTitle ?? 'Module Template'
        ])

    </head>
    <body>
        @if(!ModularityVite::useHotFile(public_path('modularity.hot'))->isRunningHot())
            @include("{$MODULARITY_VIEW_NAMESPACE}::partials.icons.svg-sprite")
        @endif

        @yield('body')

        @include("{$MODULARITY_VIEW_NAMESPACE}::partials.footer")
    </body>
</html>
