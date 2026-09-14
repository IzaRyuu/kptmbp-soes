<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Student Attempt</title>
    <!-- Tailwind CSS for UI styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen p-6 font-sans text-slate-800">

    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Grading Student Attempt</h1>
                <p class="text-slate-500 text-sm">Review responses and update marks for this submission.</p>
            </div>
            <button class="px-4 py-2 border border-slate-300 bg-white rounded-lg text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 font-medium">
                <i class="fa-solid fa-arrow-left text-xs"></i> Back to Submissions
            </button>
        </div>

        <!-- STUDENT PROFILE CARD -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex justify-between items-center">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 mb-2">Student Profile</span>
                <h2 class="text-xl font-bold text-slate-900">MUHAMMAD SHAFIQ BIN MOHD RAFI (BP0722)</h2>
                <p class="text-xs text-slate-500 mt-1">Exam: haha &nbsp;•&nbsp; Submitted: 14 Sep 2026, 01:49 PM</p>
            </div>

            <!-- Total Score Indicator (Top 'Save Marks' Button Removed) -->
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Final / Overridden Score</label>
                    <input 
                        type="number" 
                        id="finalScoreInput" 
                        value="3" 
                        class="w-24 text-center text-xl font-bold px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none" 
                        placeholder="Score"
                    />
                </div>
                <!-- 
                  REMOVED: Top "Save Marks" button.
                  The total score is automatically updated when individual question marks change,
                  or can be manually overridden and submitted via the bottom "Save All Marks" button.
                -->
            </div>
        </div>

        <!-- QUESTIONS & STUDENT ANSWERS SECTION -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-blue-600"></i> Questions & Student Answers
            </h3>

            <!-- Form container wrapping all questions -->
            <form id="gradingForm" onsubmit="handleSaveAllMarks(event)" class="space-y-6">

                <!-- Q1 CARD -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-slate-900 text-lg">Q1.</span>
                            <p class="font-medium text-slate-800 mt-0.5">hello</p>
                        </div>
                        <div class="flex gap-2 text-xs font-bold">
                            <span class="bg-slate-200 text-slate-700 px-2 py-1 rounded uppercase">MCQ</span>
                            <span class="bg-slate-900 text-white px-2 py-1 rounded">1 pt</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Options & Student Selection:</span>
                        <div class="border border-emerald-300 bg-emerald-50/50 p-3 rounded-lg flex justify-between items-center text-emerald-900 font-medium">
                            <label class="flex items-center gap-2">
                                <input type="radio" checked disabled class="accent-emerald-600"> 1
                            </label>
                            <span class="text-xs bg-emerald-200 text-emerald-800 font-bold px-2 py-0.5 rounded">Correct Answer</span>
                        </div>
                        <div class="border border-slate-200 p-3 rounded-lg text-slate-600"><input type="radio" disabled> 2</div>
                        <div class="border border-slate-200 p-3 rounded-lg text-slate-600"><input type="radio" disabled> 3</div>
                        <div class="border border-slate-200 p-3 rounded-lg text-slate-600"><input type="radio" disabled> 4</div>
                    </div>

                    <!-- Award Mark Q1 -->
                    <div class="flex justify-between items-center pt-3 border-t border-slate-100 bg-amber-50/40 -mx-6 -mb-6 p-4 rounded-b-xl">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-award text-amber-500"></i> Award Marks for Q1:
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number" 
                                name="mark_q1" 
                                data-max="1" 
                                value="1" 
                                min="0" 
                                max="1" 
                                class="question-mark-input w-20 text-center font-bold px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                onchange="recalculateTotalScore()"
                            >
                            <span class="text-slate-500 text-sm font-medium">/ 1</span>
                        </div>
                    </div>
                </div>

                <!-- Q2 CARD -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-slate-900 text-lg">Q2.</span>
                            <p class="font-medium text-slate-800 mt-0.5">sdsd</p>
                        </div>
                        <div class="flex gap-2 text-xs font-bold">
                            <span class="bg-slate-200 text-slate-700 px-2 py-1 rounded uppercase">SHORT_ANSWER</span>
                            <span class="bg-slate-900 text-white px-2 py-1 rounded">1 pt</span>
                        </div>
                    </div>

                    <div class="bg-blue-50/50 border border-blue-200 p-4 rounded-lg text-sm">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block mb-1">Expected Answer / Keywords:</span>
                        <p class="font-medium text-slate-800">1. kambing</p>
                    </div>

                    <!-- Award Mark Q2 -->
                    <div class="flex justify-between items-center pt-3 border-t border-slate-100 bg-amber-50/40 -mx-6 -mb-6 p-4 rounded-b-xl">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-award text-amber-500"></i> Award Marks for Q2:
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number" 
                                name="mark_q2" 
                                data-max="1" 
                                value="1" 
                                min="0" 
                                max="1" 
                                class="question-mark-input w-20 text-center font-bold px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                onchange="recalculateTotalScore()"
                            >
                            <span class="text-slate-500 text-sm font-medium">/ 1</span>
                        </div>
                    </div>
                </div>

                <!-- Q3 CARD -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-slate-900 text-lg">Q3.</span>
                            <p class="font-medium text-slate-800 mt-0.5">asdasd</p>
                        </div>
                        <div class="flex gap-2 text-xs font-bold">
                            <span class="bg-slate-200 text-slate-700 px-2 py-1 rounded uppercase">SHORT_ANSWER</span>
                            <span class="bg-slate-900 text-white px-2 py-1 rounded">1 pt</span>
                        </div>
                    </div>

                    <div class="bg-blue-50/50 border border-blue-200 p-4 rounded-lg text-sm">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block mb-1">Expected Answer / Keywords:</span>
                        <p class="font-medium text-slate-800">suratann takdir</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg text-sm space-y-2">
                        <div class="flex justify-between items-center text-xs font-bold text-blue-600 uppercase">
                            <span><i class="fa-regular fa-pen-to-square"></i> Student Response:</span>
                            <span class="bg-white px-2 py-0.5 rounded border border-blue-200">Word Count: 255</span>
                        </div>
                        <p class="text-blue-900 break-words font-mono text-xs leading-relaxed">
                            dsd git add . git commit -m "Fix raw HTML p tags in question text display" git push origin main...
                        </p>
                    </div>

                    <!-- Award Mark Q3 -->
                    <div class="flex justify-between items-center pt-3 border-t border-slate-100 bg-amber-50/40 -mx-6 -mb-6 p-4 rounded-b-xl">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-award text-amber-500"></i> Award Marks for Q3:
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number" 
                                name="mark_q3" 
                                data-max="1" 
                                value="1" 
                                min="0" 
                                max="1" 
                                class="question-mark-input w-20 text-center font-bold px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                onchange="recalculateTotalScore()"
                            >
                            <span class="text-slate-500 text-sm font-medium">/ 1</span>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM SAVE ALL MARKS BUTTON CONTAINER -->
                <div class="flex justify-end pt-4 pb-12">
                    <button 
                        type="submit" 
                        id="saveAllMarksBtn"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition flex items-center gap-2.5 text-base"
                    >
                        <i class="fa-solid fa-floppy-disk"></i> Save All Marks
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- JAVASCRIPT FOR CALCULATING AND SAVING ALL MARKS -->
    <script>
        // Automatically recalculate top score whenever individual marks change
        function recalculateTotalScore() {
            const inputs = document.querySelectorAll('.question-mark-input');
            let total = 0;
            inputs.forEach(input => {
                const val = parseFloat(input.value) || 0;
                total += val;
            });
            document.getElementById('finalScoreInput').value = total;
        }

        // Handles saving all marks when the lecturer clicks "Save All Marks"
        async function handleSaveAllMarks(event) {
            event.preventDefault();

            const saveBtn = document.getElementById('saveAllMarksBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Saving...`;

            // Gather all individual marks
            const markInputs = document.querySelectorAll('.question-mark-input');
            const questionMarks = {};

            markInputs.forEach(input => {
                questionMarks[input.name] = parseFloat(input.value) || 0;
            });

            const finalScore = parseFloat(document.getElementById('finalScoreInput').value) || 0;

            // Construct payload to send to your API endpoint
            const payload = {
                studentId: "BP0722",
                finalScore: finalScore,
                marks: questionMarks
            };

            console.log("Submitting Payload to Server:", payload);

            try {
                /* Replace with your actual backend API call:
                const response = await fetch('/api/submissions/grade', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                */
                
                // Simulated API delay for demonstration
                await new Promise(resolve => setTimeout(resolve, 800));

                alert('All marks saved successfully!');
            } catch (error) {
                console.error("Error saving marks:", error);
                alert('Failed to save marks. Please try again.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Save All Marks`;
            }
        }
    </script>
</body>
</html>
