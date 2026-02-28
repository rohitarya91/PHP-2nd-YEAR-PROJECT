<?php
session_start();

/* Demo Data (baad me database se laa sakte ho) */
$_SESSION['name'] = "Rahul";
$_SESSION['email'] = "rahul@gmail.com";
$_SESSION['role'] = "User";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4>My Account</h4>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Name:</div>
                <div class="col-md-9"><?php echo $_SESSION['name']; ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Email:</div>
                <div class="col-md-9"><?php echo $_SESSION['email']; ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Role:</div>
                <div class="col-md-9"><?php echo $_SESSION['role']; ?></div>
            </div>

            <a href="user_dashbord.php" class="btn btn-secondary">Back</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

</div>

</body>
</html>