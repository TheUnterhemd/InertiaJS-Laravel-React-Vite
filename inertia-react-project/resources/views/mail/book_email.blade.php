<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
</head>
<body>
    <h1>{{ $body['title'] }}</h1>
    {!! $body['content'] !!}
    <img src="{{ $message->embed($imagePath) }}" alt="{{ $bookTitle }}" />
</body>
</html>