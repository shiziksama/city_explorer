<!DOCTYPE html>
<html>
    <head>
	<meta name="viewport" content="width=device-width" />
    @yield('head')
    </head>
    <body  lang="ru">
	 @yield('content')
	 @stack('scripts')
    </body>
</html>