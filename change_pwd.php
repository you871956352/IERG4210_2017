<?php
	include_once('lib/csrf.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <title>Online Shop Password Change Page</title>

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
    <legend>Change Password Forms</legend>
    <form method="POST" action="auth-process.php?action=<?php echo ($action = 'changePwd'); ?>">
        <label for="user_email">Input Email:</label>
        <input type="email" name="user_email" required="true" pattern="^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$" />
        <p></p>
        <label for="old_pw">Old Password:</label>
        <input type="password" name="old_pw" required="true" pattern="^[A-Za-z_\d]\w{2,19}$" />
        <p></p>
        <label for="new_pw">New Password:</label>
        <input type="password" name="new_pw" required="true" pattern="^[A-Za-z_\d]\w{2,19}$" />
        <p></p>
        <label for="r_new_pw">Repeat New_psd:</label>
        <input type="password" name="r_new_pw" required="true" pattern="^[A-Za-z_\d]\w{2,19}$" />

        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />
        <input type="submit" value="Confirm" />
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
