<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - OctuTune</title>
  <link href="https://fonts.googleapis.com/css2?family=Aptos:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.css">
</head>
<body class="font-aptos">
  <div class="d-flex justify-content-center align-items-center bg-dark" style="height: 100vh;">
    <div class="container p-5 rounded bg-purp shadow">
      <h1 class="text-white">Login</h1>
      <form action="login.php" method="post" class="d-flex flex-column text-white">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" class="rounded border-solid" required>
        <label for="password">Password:</label>
        <input type="password" class="rounded border-solid" name="password" id="password" required>
        <button type="submit" class="rounded border-solid">Login</button>
      </form>
    </div>
  </div>
</body>
</html>