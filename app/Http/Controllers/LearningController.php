<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\StudentAnswer;
use App\Models\CourseQuestion;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller 
{
    public function index(){

        $user = Auth::user();

        $my_courses = $user->courses()->with('category')->orderBy('id', 'DESC')->get();

        foreach($my_courses as $course){
            $totalQuestionCount = $course->questions()->count();


            $answerQuestionCount = StudentAnswer::where('user_id', $user->id)
            ->whereHas('question', function ($query) use($course){
                $query->where('course_id', $course->id);
            })->distinct()->count('course_question_id');


            if($answerQuestionCount < $totalQuestionCount) {
                $firstUnansweredQuestion = CourseQuestion::where('course_id', $course->id)
                ->whereNotIn('id', function ($query) use ($user) {
                    $query->select('course_question_id')->from('student_answers')
                    ->where('user_id', $user->id);
                })->orderBy('id', 'asc')->first();

                $course->nextQuestionId = $firstUnansweredQuestion ? $firstUnansweredQuestion->id : null;
            }
            else{
                $course->nextQuestionId = null;
            }
        }


        return view('student.course.index', [
            'my_courses'=> $my_courses,
        ]);
    }

    public function learning(Course $course, $question){
        $user = Auth::user();

        $isEnrolled = $user->courses()->where('course_id', $course->id)->exists();

        if(!$isEnrolled){
            abort(404);
        }

        $currentQuestion = CourseQuestion::where('course_id', $course->id)->where('id', $question)->firstOrFail();

        return view('student.course.learning', [
            'course'=> $course,
            'question' => $currentQuestion,
            
        ]);
    }

    public function learning_finished(Course $course){
        return view('student.course.learning_finished', [
            'course'=> $course,
            
            
        ]);
    }

    public function learning_rapport(Course $course){

        $userId = Auth::id();

        $studentAnswers = StudentAnswer::with('question')
        ->whereHas('question', function ($query) use ($course){
            $query->where('course_id', $course->id);
        })->where('user_id', $userId)->get();

        $totalQuestions = CourseQuestion::where('course_id', $course->id)->count();
        $correctAnswerCount = $studentAnswers ->where('answer', 'correct')->count();
        $passed = $correctAnswerCount == $totalQuestions;

        return view('student.course.learning_rapport', [
            'course' => $course,
            'passed' => $passed,
            'totalQuestions' => $totalQuestions,
            'correctAnswerCount' => $correctAnswerCount,
            'studentAnswers' => $studentAnswers,
        ]);
    }
}
