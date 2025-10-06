<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'RWAMP Newsletter' }}</title>
    <style> body{font-family: Arial, Helvetica, sans-serif;} </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <div>
        {!! nl2br(e($content ?? '')) !!}
    </div>
    <hr>
    <small>You are receiving this because you subscribed with {{ $subscriber->email ?? '' }}.</small>
</body>
</html>
