<!DOCTYPE html>
<html>
<body>
    <h2>SharpFleet Login</h2>

    <form method="POST" action="/app/sharpfleet/login">
        @csrf

        <div>
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    @if($errors->any())
        <p style="color:red">{{ $errors->first() }}</p>
    @endif
</body>
</html>
