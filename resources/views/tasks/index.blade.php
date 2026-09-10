<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seznam úkolů</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 560px; margin: 40px auto; padding: 0 16px; color: #1a1a1a; }
        h1 { font-size: 22px; }
        form.add { display: flex; gap: 8px; margin: 16px 0; }
        input[type=text] { flex: 1; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        button { padding: 8px 12px; border: 0; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; font-size: 14px; }
        button.link { background: none; color: #2563eb; padding: 4px; }
        button.danger { background: none; color: #dc2626; padding: 4px; }
        ul { list-style: none; padding: 0; }
        li { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee; }
        li .title { flex: 1; }
        li.done .title { text-decoration: line-through; color: #999; }
        .msg { background: #dcfce7; color: #166534; padding: 8px 10px; border-radius: 6px; }
        .error { color: #dc2626; font-size: 13px; }
    </style>
</head>
<body>
    <h1>Seznam úkolů</h1>

    @if (session('status'))
        <p class="msg">{{ session('status') }}</p>
    @endif

    <form class="add" method="POST" action="{{ route('tasks.store') }}">
        @csrf
        <input type="text" name="title" placeholder="Nový úkol…" value="{{ old('title') }}" autofocus>
        <button type="submit">Přidat</button>
    </form>
    @error('title')
        <p class="error">{{ $message }}</p>
    @enderror

    <ul>
        @forelse ($tasks as $task)
            <li class="{{ $task->done ? 'done' : '' }}">
                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="link">{{ $task->done ? '↩︎' : '✓' }}</button>
                </form>
                <span class="title">{{ $task->title }}</span>
                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Smazat úkol?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger">✕</button>
                </form>
            </li>
        @empty
            <li>Zatím žádné úkoly.</li>
        @endforelse
    </ul>
</body>
</html>
