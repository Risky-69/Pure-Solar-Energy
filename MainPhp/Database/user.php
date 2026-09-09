<?php

require 'database/config.php';

$pdo  = getConnection();
$sql  = "SELECT id, username, email, age FROM user ORDER BY id ASC";
$stmt = $pdo->query($sql);
$user = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Users</title>
</head>

<body>

    <h1>All Users</h1>

    <!-- <p><a href="index.php">Register a new User</a></p> -->

    <!-- <?php if (empty($user)): ?> -->

        <p>No User registered yet.</p>

    <?php else: ?>

        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <!-- <th>ID</th> -->
                <th>Username</th>
                <th>Password</th>
                <!-- <th>Age</th> -->
            </tr>
            <?php foreach ($user as $user): ?>
                <tr>
                    <!-- <td><?= htmlspecialchars($student['id']) ?></td> -->
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <!-- <td><?= htmlspecialchars($student['age']) ?></td> -->
                </tr>
            <?php endforeach; ?>
        </table>

    <?php endif; ?>

</body>

</html>