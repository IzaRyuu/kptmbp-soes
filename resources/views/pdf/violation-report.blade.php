<!DOCTYPE html>
<html>
<head>
    <title>Violation Evidence Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .badge { padding: 3px 6px; color: white; background-color: #dc3545; border-radius: 3px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">OFFICIAL EXAM VIOLATION EVIDENCE REPORT</div>
        <p><strong>System:</strong> KPTMBP Secure Online Examination System (SoES)</p>
    </div>

    <p><strong>Exam Title:</strong> {{ $exam->title }}</p>
    <p><strong>Course:</strong> 
        {{ 
            $exam->course->course_name 
            ?? $exam->course->course_code 
            ?? $exam->class->class_name 
            ?? 'General Exam' 
        }}
    </p>
    <p><strong>Total Violations Recorded:</strong> {{ $exam->violations->count() }}</p>
    <p><strong>Generated Date:</strong> {{ now()->format('d M Y, h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Timestamp</th>
                <th>Student Name</th>
                <th>Violation Type</th>
                <th>Severity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exam->violations as $index => $violation)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($violation->occurred_at ?? $violation->created_at)->format('d M Y, h:i A') }}</td>
                    <td>{{ $violation->student->user->name ?? $violation->student->name ?? }} ({{ $violation->student->matric_no ?? 'N/A' }})</td>
                    <td>{{ str_replace('_', ' ', strtoupper($violation->violation_type ?? 'TAB SWITCH')) }}</td>
                    <td><span class="badge">HIGH</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>