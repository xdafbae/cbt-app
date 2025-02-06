<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::orderBy('id', 'DESC')->get();
        return view('admin.courses.index', [
            'courses' => $courses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::User();
        $categories = Category::all();
        return view('admin.courses.create', [
            'categories' => $categories,
            'user' => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'cover' => 'required|image|mimes:png,jpg,svg',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('cover')) {
                $coverPath = $request->file('cover')->store('product_covers', 'public');
                $validate['cover'] = $coverPath;
            }

            $validate['slug'] = Str::slug($request->name);
            $newCourse = Course::create($validate);

            DB::commit();

            return redirect()->route('dashboard.courses.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {

        $students = $course->students()->orderBy('id', 'DESC')->get();
        $questions = $course->questions()->orderBy('id', 'DESC')->get();

        return view ('admin.courses.manage', [
            'course' => $course,
            'students'=> $students,
            'questions'=> $questions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        //
        $categories = Category::all();
        return view('admin.courses.edit', [
            'course' => $course,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        //
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'cover' => 'sometimes|image|mimes:png,jpg,svg',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('cover')) {
                $coverPath = $request->file('cover')->store('product_covers', 'public');
                $validate['cover'] = $coverPath;
            }

            $validate['slug'] = Str::slug($request->name);

            $course->update($validate);
            DB::commit();

            return redirect()->route('dashboard.courses.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        //
        try{
            $course->delete();
            return redirect()->route('dashboard.courses.index');
 
        }
        catch (\Exception $e) {
            DB::rollBack();
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
