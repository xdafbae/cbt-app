<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\CourseStudent;
use App\Models\StudentAnswer;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Dotenv\Exception\ValidationException;

class CourseStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        //
        $students = $course->students()->orderBy('id', 'DESC')->get();
        $questions = $course->questions()->orderBy('id', 'DESC')->get();

        $totalQuestions = $questions->count();

        foreach($students as $student){
            $studentAnswers = StudentAnswer::whereHas('question', function ($query) use ($course){
                $query->where('course_id', $course->id);
            })->where('user_id', $student->id)->get();

            $answersCount = $studentAnswers->count();
            $correctAnswersCount = $studentAnswers->where('answer', 'correct')->count();

            if($answersCount == 0) {
                $student->status = 'Not Started';
            }elseif ($correctAnswersCount < $totalQuestions){
                $student->status = 'Not Passed';

            }elseif ($correctAnswersCount == $totalQuestions){
                $student->status = 'Passed';
            }
        }

        return view('admin.students.index', [
            'course' => $course,
            'questions' => $questions,
            'students' => $students,
              
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        //
        $students = $course->students()->orderBy('id', 'DESC')->get();

        return view('admin.students.add_student', [
            'course' => $course,
            'students' => $students,
              
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course)
    {
        //
        $request->validate([
            'email' => 'required|string',

        ]);
        $user = User::where('email', $request->email)->first();

        if(!$user){
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'sytem error' => ['Email student tidak tersedia!'],
            ]);
            throw $error;
        }

        $isEnrolled = $course->students()->where('user_id', $user->id)->exists();

        if($isEnrolled){
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'sytem error' => ['Student sudah memiliki hak akses kelas!'],
            ]);
            throw $error;
        }

        DB::beginTransaction();

        try{
            $course->students()->attach($user->id);
            DB::commit();
            return redirect()->route('dashboard.course.course_students.index', $course);
        }
        catch (\Exception $e) {
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
    public function show(CourseStudent $courseStudent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseStudent $courseStudent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseStudent $courseStudent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseStudent $courseStudent)
    {
        //
    }
}
