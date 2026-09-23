<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 520px; margin: 40px auto; padding: 0 16px; color: #1a1a1a; }
        nav { display: flex; gap: 16px; margin-bottom: 24px; }
        nav a { color: #2563eb; text-decoration: none; }
        nav a.active { font-weight: 600; text-decoration: underline; }
        h1 { font-size: 22px; }
        form.add { display: flex; gap: 8px; margin: 16px 0; }
        input[type=text] { flex: 1; min-width: 0; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        button { padding: 8px 14px; border: 0; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; font-size: 14px; }
        button.small { padding: 4px 10px; font-size: 13px; background: #eee; color: #1a1a1a; }
        button.danger { background: #fee2e2; color: #b91c1c; }
        ul { list-style: none; padding: 0; }
        li { display: flex; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eee; }
        li .text { flex: 1; }
        li form { margin: 0; }
        .done { text-decoration: line-through; color: #999; }
        .muted { color: #777; }
        .error { color: #b91c1c; font-size: 14px; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}">Úkoly</a>
        <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}">Studenti</a>
    </nav>

    @yield('content')
</body>
</html>
