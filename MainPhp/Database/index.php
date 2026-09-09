<?php
$status  = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;
// $id      = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<!-- <head>
    <meta charset="UTF-8">
    <title>Register Student</title>
</head> -->

<body>

    <!-- <h1>Register a Student</h1> -->

    <?php if ($status === 'success'): ?>
        <p style="color: green;">Student registered successfully! New ID: <?= htmlspecialchars($id) ?></p>
    <?php elseif ($status === 'error'): ?>
        <p style="color: red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="function.php">
        <label>Username</label><br>
        <input type="text" name="username"><br><br>

        <label>Password</label><br>
        <input type="Password" name="Password"><br><br>
<!-- 
        <label>Age</label><br>
        <input type="number" name="age"><br><br> -->

        <button type="submit" name="add-student">Register</button>
    </form>

</body>

</html>