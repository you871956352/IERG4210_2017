<?php
	include_once('lib/csrf.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <title>Online Shop Signup Page</title>

    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Other CSS adjustments -->
    <link href="css/shop.css" rel="stylesheet">

</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">OnlineShop</a>
    </nav>

    <fieldset>
        <legend>Login Form</legend>
        <form id="loginForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'signup'); ?>">
            <label for="email">Email:</label>
            <input type="email" name="email" required="true" pattern="^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$" />
            <label for="pw">Password:</label>
            <input type="password" name="pw" required="true" pattern="^[A-Za-z_\d]\w{2,19}$" />
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />
            <input type="submit" class="btn btn-primary" value="Signup" />
        </form>
    </fieldset>
    
    <!-- Footer -->

    <footer class="py-4 bg-dark footer">
        <div class="container">
            <p class="m-0 text-center text-white">Copyright &copy; KavinYou_2017</p>
        </div>
    </footer>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/popper/popper.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>
