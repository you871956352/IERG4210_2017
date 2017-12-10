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
        </nav>

        <!-- Main content-->
        <div class="container mt-4 mb-6">

            <ul id="purchased-list">
                <p>Purchased List: </p>
                <?php
                $username = loggedin();
                echo "Username : ".$username;
                //echo $username;
                $db = new PDO('sqlite:/var/www/cart.db');
                $q = $db->query("SELECT oid FROM orders ORDER BY oid DESC LIMIT 100");
                $oids = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                $q = $db->query("SELECT createdtime FROM orders ORDER BY oid DESC LIMIT 100");
                $createdtimes = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                $q = $db->query("SELECT status FROM orders ORDER BY oid DESC LIMIT 100");
                $status = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                $q = $db->query("SELECT username FROM orders ORDER BY oid DESC LIMIT 100");
                $users = $q->fetchAll(PDO::FETCH_COLUMN, 0);

    $totalIncome = 0.0;
    $j = 0;
    for ($i = 0, $len = count($oids); ($i < $len)&&($j < 10); $i++) {
        if ($users[$i] == $username){
            echo "<li>OrderID:".$oids[$i]."&emsp;".$createdtimes[$i]."&emsp;".$status[$i]."</li>";
        if ($status[$i] == 'Paid')
        {
            $q = $db->query("SELECT txn_id FROM orders WHERE oid = $oids[$i]");
            $txn_id = $q->fetchAll(PDO::FETCH_COLUMN, 0);

            $q = $db->prepare("SELECT * FROM purchased_list WHERE txn_id = ?");
            $q->execute(array($txn_id[0]));
            $p_pids = $q->fetchAll(PDO::FETCH_COLUMN, 1);
            $q->execute(array($txn_id[0]));
            $p_quan = $q->fetchAll(PDO::FETCH_COLUMN, 2);
            $q->execute(array($txn_id[0]));
            $p_price = $q->fetchAll(PDO::FETCH_COLUMN, 3);

            $sum = 0.0;
            for ($ind1 = 0, $leng1 = count($p_quan); $ind1 < $leng1; $ind1++) {
                $sum += $p_price[$ind1];
            }
            echo "&emsp;SumPrice:".$sum."HKD<br>";
            echo "&emsp;Product List:<br>";
            for ($ind = 0, $leng = count($p_pids); $ind < $leng; $ind++) {
                $q = $db->prepare("SELECT name FROM products WHERE pid = ?");
                $q->execute(array($p_pids[$ind]));
                $pname = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                echo "&emsp;&emsp;Item".$ind.":".$pname[0]."&emsp;Amount:".$p_quan[$ind]."&emsp;Subtotal:".$p_price[$ind]."HKD<br>";
           }
            echo "<br>";
            $totalIncome += $sum;
        }
            $j++;
        }
    }
 //   echo "<p id='totalIncome'>Total Income: ".$totalIncome."HKD</p>";
    ?>
            </ul>

        </div>
        <!-- Footer -->

        <footer class="py-4 bg-dark footer">
            <div class="container">
                <p class="m-0 text-center text-white">Copyright &copy; KavinYou_2017</p>
            </div>
        </footer>

        <!-- Bootstrap core JavaScript -->
        <script type="text/javascript" src="js/myLib.js"></script>
        <script type="text/javascript" src="js/cart.js"></script>
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/popper/popper.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    </body>

    </html>
