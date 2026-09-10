<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Zobrazí seznam všech úkolů.
     */
    public function index()
    {
        $tasks = Task::latest()->get();

        return view('tasks.index', ['tasks' => $tasks]);
    }

    /**
     * Uloží nový úkol a vrátí zpět na seznam.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        Task::create($data);

        return redirect()->route('tasks.index');
    }
}
