<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Iniciar Sesión')</title>

  <!-- Tailwind CSS (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    // Config mínima para mantener consistencia visual con el dashboard
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: [
              "Inter var",
              "Inter",
              "system-ui",
              "-apple-system",
              "Segoe UI",
              "Roboto",
              "Ubuntu",
              "Cantarell",
              "Noto Sans",
              "Helvetica Neue",
              "Arial",
              '"Apple Color Emoji"',
              '"Segoe UI Emoji"',
              '"Segoe UI Symbol"'
            ]
          },
          boxShadow: {
            soft: "0 1px 2px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.10)"
          }
        }
      }
    }
  </script>
</head>
<body class="min-h-screen h-full bg-slate-50 text-slate-900 font-sans antialiased">
  @yield('content')
</body>
</html>
