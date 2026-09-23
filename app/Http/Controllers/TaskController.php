<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * seznam ukolu
     */
    public function index(): View
    {
        $tasks = Task::orderByDesc('id')->get();

        return view('tasks.index', ['tasks' => $tasks]);
    }

    /**
     * ulozi ukol
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        Task::create($data);

        return redirect()->route('tasks.index');
    }

    /**
     * prepne hotovo / nehotovo
     */
    public function toggle(Task $task): RedirectResponse
    {
        $task->update(['is_done' => ! $task->is_done]);

        return redirect()->route('tasks.index');
    }

    /**
     * smaze ukol
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index');
    }
}
