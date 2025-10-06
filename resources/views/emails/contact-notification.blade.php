<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Submission - RWAMP</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; }
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <h2>New Contact Submission</h2>
    <p>You have received a new message from the website contact form.</p>
    <ul>
        <li><strong>Name:</strong> {{ $contact->name }}</li>
        <li><strong>Email:</strong> {{ $contact->email }}</li>
        @if($contact->phone)
            <li><strong>Phone:</strong> {{ $contact->phone }}</li>
        @endif
    </ul>
    <p><strong>Message:</strong></p>
    <p>{{ $contact->message }}</p>
</body>
</html>


