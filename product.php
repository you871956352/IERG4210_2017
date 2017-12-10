<?php

    include_once('lib/db.inc.php');
	include_once('lib/csrf.php');
	session_regenerate_id();

	function loggedin()
	{
		if (!empty($SESSION['t4210']))
			return $_SESSION['t4210']['em'];
		if (!empty($_COOKIE['t4210'])) {
			// stripslashes returns a string with backslashes stripped off.
			//(\' becomes ' and so on)
			if ($t = json_decode(stripslashes($_COOKIE['t4210']), true)) {
				if (time() > $t['exp']) return false;
				$db = ierg4210_DB();
				$q = $db->prepare("SELECT * FROM account WHERE email = ?");
				$q->execute(array($t['em']));
				if ($r = $q->fetch()) {
					$realk = hash_hmac('sha1', $t['exp'] . $r['password'], $r['salt']);
					if ($realk == $t['k']) {
						$_SESSION['t4210'] = $t;
						return $t['em'];
					}
				}
			}
		}
		return false;
	}

	if (!loggedin()) {
		// redirect to login
		header('Location:login.php');
		exit();
	}


    parse_str($_SERVER['QUERY_STRING']);
	$db = new PDO('sqlite:/var/www/cart.db');

	$q = $db->query("SELECT catid FROM categories");
	$catID = $q->fetchAll(PDO::FETCH_COLUMN,0);
	$q = $db->query("SELECT name FROM categories");
	$catName = $q->fetchAll(PDO::FETCH_COLUMN,0);

    $q = $db->query("SELECT name FROM products WHERE pid = $pid");
    $prod_name = $q->fetchAll(PDO::FETCH_COLUMN, 0);
    $q = $db->query("SELECT price FROM products WHERE pid = $pid");
    $prod_price = $q->fetchAll(PDO::FETCH_COLUMN, 0);
    $q = $db->query("SELECT description FROM products WHERE pid = $pid");
    $prod_desc = $q->fetchAll(PDO::FETCH_COLUMN, 0);
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Online Shop</title>

        <!-- Bootstrap core CSS -->
        <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- Other CSS adjustments -->
        <link href="css/shop.css" rel="stylesheet">

    </head>

    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="index.php">OnlineShop</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">

                    <li class="nav-item">
                        <div class="dropdown"> <a class="nav-link dropdown-toggle" id="dropdownMenuButton" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Shopping-list</a>
                            <div class="dropdown-menu" id="cartUL"></div>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">AdminPage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">UserOrder</a>
                    </li>

                </ul>
                
                <form class="form-inline my-2 my-lg-0" method="POST" action="auth-process.php?action=<?php echo ($action = 'logout'); ?>">
                    <input class="username form-control mr-sm-2" type="text" readonly="readonly" value="<?php echo loggedin();?>" />
                    <input class="btn btn-outline-success my-2 my-sm-0 mr-sm-2 logoutForm" type="submit" value="Log Out" />
                    <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />
                </form>
                <a class="btn btn-outline-success my-2 my-sm-0" href="change_pwd.php">Change PWD</a>
                
            </div>
        </nav>

        <!-- Main content-->
        <div class="container mb-6">

            <div class="row">
                <div class="col-lg">
                    <?php
                            echo '
                            <h1 class="my-4"><a class="black" href=index.php>Home</a> > 
                            <a class="black" href="index.php?catid='.$catid.'">Category '.$catName[$catid-1].'</a> > 
                            <a class="black" href="product.php?pid='.$pid.'&catid='.$catid.'">Product '.$prod_name[0].'</a></h1>';
                    ?>
                </div>
            </div>

            <div class="row">

                <div class="col-lg-3">

                    <div class="list-group">
                        <?php
                        for($i = 0,$l = count($catID);$i < $l;$i ++){
                            echo '<a href="index.php?catid='.$catID[$i].'" class="list-group-item">Category '.$catName[$i].'</a>';
                        }
                        ?>
                    </div>

                </div>


                <div class="col-lg-9">
                    <div class="card mb-5">
                        <?php
                            echo '<img class="card-img-top" src="img/'.$pid.'.jpg" alt="Card image cap">';
                            echo '
                            <div class="card-body">
                                <h4 class="card-title">Product '.$prod_name[0].'</h4>
                                <h6 class="card-subtitle text-muted">Price $'.$prod_price[0].'</h6>
                                <p class="card-text">'.$prod_desc[0].'</p>
                                <a href="#" class="btn btn-primary" onclick="addToCart('.$pid.')">Add to Cart</a>
                            </div>
                            ';
                        ?>
                    </div>
                </div>

            </div>

        </div>
        <!-- Footer -->

        <footer class="py-4 bg-dark footer">
            <div class="container">
                <p class="m-0 text-center text-white">Copyright &copy; KavinYou_2017</p>
            </div>
        </footer>

        <!-- Bootstrap core JavaScript -->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/popper/popper.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/myLib.js"></script>
        <script type="text/javascript" src="js/cart.js"></script>
    </body>

    </html>
