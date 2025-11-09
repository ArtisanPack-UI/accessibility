<!DOCTYPE html>
<html>
<head>
    <title>Accessibility Compliance Certificate</title>
    <style>
        body { font-family: sans-serif; }
        .certificate { border: 10px solid #eee; padding: 50px; text-align: center; }
        .organization { font-size: 24px; font-weight: bold; }
        .score { font-size: 48px; font-weight: bold; margin: 20px 0; }
        .date { font-size: 18px; }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Accessibility Compliance Certificate</h1>
        <p>This is to certify that</p>
        <div class="organization">{{ $report->organization->name }}</div>
        <p>has achieved an accessibility score of</p>
        <div class="score">{{ $report->score }}</div>
        <p>on</p>
        <div class="date">{{ $report->created_at->toFormattedDateString() }}</div>
    </div>
</body>
</html>
