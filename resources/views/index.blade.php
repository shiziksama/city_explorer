<!DOCTYPE html>
<html>
    <head>
    @yield('head')
    </head>
    <body  lang="ru">
	 @yield('content')
	 @stack('scripts')
    </body>
</html>