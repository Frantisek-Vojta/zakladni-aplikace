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
        $tasks = Task::orderBy('done')->latest()->get();

        return view('tasks.index', ['tasks' => $tasks]);
    }

    /**
     * Uloží nový úkol.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        Task::create($data);

        return redirect()->route('tasks.index')->with('status', 'Úkol byl přidán.');
    }

    /**
     * Přepne úkol mezi splněno / nesplněno.
     */
    public function toggle(Task $task)
    {
        $task->update(['done' => ! $task->done]);

        return redirect()->route('tasks.index');
    }

    /**
     * Smaže úkol.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Úkol byl smazán.');
    }
}
