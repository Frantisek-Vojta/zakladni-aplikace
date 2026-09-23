<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * seznam studentu
     */
    public function index(): View
    {
        $students = Student::orderBy('class')->orderBy('name')->get();

        return view('students.index', ['students' => $students]);
    }

    /**
     * ulozi studenta
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class' => ['required', 'string', 'max:10'],
        ]);

        Student::create($data);

        return redirect()->route('students.index');
    }

    /**
     * smaze studenta
     */
    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index');
    }
}
