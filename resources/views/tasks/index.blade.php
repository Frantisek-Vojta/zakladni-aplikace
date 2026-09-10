<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seznam úkolů</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 480px; margin: 40px auto; padding: 0 16px; color: #1a1a1a; }
        h1 { font-size: 22px; }
        form { display: flex; gap: 8px; margin: 16px 0; }
        input[type=text] { flex: 1; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        button { padding: 8px 14px; border: 0; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; font-size: 14px; }
        ul { list-style: none; padding: 0; }
        li { padding: 10px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <h1>Seznam úkolů</h1>

    <form method="POST" action="{{ route('tasks.store') }}">
        @csrf
        <input type="text" name="title" placeholder="Nový úkol…" autofocus>
        <button type="submit">Přidat</button>
    </form>

    <ul>
        @forelse ($tasks as $task)
            <li>{{ $task->title }}</li>
        @empty
            <li>Zatím žádné úkoly.</li>
        @endforelse
    </ul>
</body>
</html>
