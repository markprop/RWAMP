<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Reseller Application - RWAMP</title>
    <style> body{font-family: Arial, Helvetica, sans-serif;} </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <h2>New Reseller Application</h2>
    <ul>
        <li><strong>Name:</strong> {{ $application->name }}</li>
        <li><strong>Email:</strong> {{ $application->email }}</li>
        <li><strong>Phone:</strong> {{ $application->phone }}</li>
        @if($application->company)
            <li><strong>Company:</strong> {{ $application->company }}</li>
        @endif
        <li><strong>Investment Capacity:</strong> {{ $application->investment_capacity }}</li>
    </ul>
    @if($application->message)
        <p><strong>Message:</strong></p>
        <p>{{ $application->message }}</p>
    @endif
</body>
</html>


