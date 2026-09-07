<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Exam;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

class LecturerQuestionController extends Controller
{
    /**
     * Display Question Bank
     */
    public function index($examId)
    {
        $exam = Exam::with([
            'class',
            'questions.options'
        ])->findOrFail($examId);

        return view('lecturer.questions', compact('exam'));
    }

    /**
     * Store Question + MCQ Options
     */
    public function store(Request $request, $examId)
    {
        $request->validate([
            'question_text'       => 'required|string',
            'question_type'      => 'required|in:mcq,short_answer',
            'points'             => 'required|integer|min:1',
            'options'            => 'nullable|array',
            'options.*'          => 'nullable|string',
            'correct_option'     => 'nullable|integer',
            'correct_answer_text' => 'nullable|string',
        ]);

        try {

            DB::transaction(function () use ($request, $examId) {

                // Create question
                $question = Question::create([
                    'exam_id'            => $examId,
                    'question_text'      => $request->question_text,
                    'question_type'      => $request->question_type,
                    'points'             => $request->points,
                    'correct_answer_text' => $request->question_type === 'short_answer'
                        ? $request->correct_answer_text
                        : null,
                ]);

                // Get question primary key
                $questionId = $question->question_id;

                // ==========================================
                // MCQ
                // ==========================================

                if ($request->question_type === 'mcq') {

                    $options = $request->input('options', []);
                    $correctIndex = (int) $request->input('correct_option', 0);

                    foreach ($options as $index => $optionText) {

                        if (trim($optionText) !== '') {

                            QuestionOption::create([
                                'question_id' => $questionId,
                                'option_text' => trim($optionText),
                                'is_correct'  => ((int) $index === $correctIndex) ? 1 : 0,
                            ]);
                        }
                    }
                }
            });

            return redirect()
                ->back()
                ->with('success', 'Question added successfully!');

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to add question: ' . $e->getMessage());
        }
    }

    /**
     * Delete Question
     */
    public function destroy($questionId)
    {
        try {

            $question = Question::findOrFail($questionId);

            // Delete options first
            QuestionOption::where(
                'question_id',
                $question->question_id
            )->delete();

            // Delete question
            $question->delete();

            return redirect()
                ->back()
                ->with('success', 'Question deleted successfully!');

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to delete question: ' . $e->getMessage()
                );
        }
    }
}