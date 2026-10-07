<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 16px; color: #1a1a1a; }
        nav { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        nav a { color: #2563eb; text-decoration: none; }
        nav a.active { font-weight: 600; text-decoration: underline; }
        h1 { font-size: 22px; }
        h2 { font-size: 17px; margin-top: 32px; }
        a { color: #2563eb; }
        form.add { display: flex; gap: 8px; margin: 16px 0; flex-wrap: wrap; }
        input[type=text], input[type=email], input[type=number], input[type=date], select { padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        input[type=text] { flex: 1; min-width: 0; }
        button { padding: 8px 14px; border: 0; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; font-size: 14px; }
        button.small { padding: 4px 10px; font-size: 13px; background: #eee; color: #1a1a1a; }
        button.danger { background: #fee2e2; color: #b91c1c; }
        ul { list-style: none; padding: 0; }
        li { display: flex; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eee; }
        li .text { flex: 1; }
        li form { margin: 0; }
        .done { text-decoration: line-through; color: #999; }
        .muted { color: #777; }
        .error { color: #b91c1c; font-size: 14px; margin: 4px 0; }
        /* tabulky pro knihy a vypujcky */
        table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 15px; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #eee; }
        th { font-size: 13px; color: #777; font-weight: 600; }
        /* formular na vice radku (pridani knihy / vypujcky) */
        form.stack { display: grid; gap: 10px; margin: 16px 0; max-width: 360px; }
        form.stack label { font-size: 14px; display: grid; gap: 4px; }
        .badge { font-size: 12px; padding: 2px 8px; border-radius: 10px; background: #eee; }
        .badge.overdue { background: #fee2e2; color: #b91c1c; }
        .badge.returned { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('books.index') }}" class="{{ request()->routeIs('books.*') ? 'active' : '' }}">Knihy</a>
        <a href="{{ route('readers.index') }}" class="{{ request()->routeIs('readers.*') ? 'active' : '' }}">Čtenáři</a>
        <a href="{{ route('loans.index') }}" class="{{ request()->routeIs('loans.*') ? 'active' : '' }}">Výpůjčky</a>
        <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}">Úkoly</a>
        <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}">Studenti</a>
    </nav>

    @yield('content')
</body>
</html>
