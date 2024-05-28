<!DOCTYPE html>
<html>
<head>
    <title>Add Account</title>
</head>
<body>
<h1>Add Outlook Account</h1>
<form action="{{ url('api/create-account') }}" method="POST">
    @csrf
    <button type="submit">Add Account</button>
</form>
</body>
</html>
