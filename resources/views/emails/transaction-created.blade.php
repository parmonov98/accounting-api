<!DOCTYPE html>
<html>
<head>
    <title>New Transaction Created</title>
</head>
<body>
    <h1>New Transaction Created</h1>
    <p>A new {{ strtolower($type) }} transaction has been created in your account.</p>
    
    <h2>Transaction Details:</h2>
    <ul>
        <li><strong>Type:</strong> {{ $type }}</li>
        <li><strong>Amount:</strong> {{ $amount }}</li>
        @if($title)
            <li><strong>Title:</strong> {{ $title }}</li>
        @endif
        <li><strong>Date:</strong> {{ $date }}</li>
    </ul>
</body>
</html>
