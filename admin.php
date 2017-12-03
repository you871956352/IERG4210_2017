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
				if ($realk == $t['k'] && $r['email'] == "admin@gmail.com") {
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
        <title>Online Shop Admin Page</title>

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
        <div class="container mt-4 mb-5">

            <fieldset>
                <legend>Choose The Operation</legend>
                <select class="form-control" id="choose">
            <option value="-1">--  Choose one operation  --</option>
            <option value="0">Add product</option>
            <option value="1">Delete product</option>
            <option value="2">Edit product</option>
            <option value="3">Add category</option>
            <option value="4">Delete category</option>
            <option value="5">Edit category</option>
        </select>
            </fieldset>

            <form class="myform myform_0" method="POST" action="admin-process.php?action=prod_insert" enctype="multipart/form-data">
                <fieldset>
                    <legend>Add Product</legend>
                    <div class="form-group">
                        <label for="prod_catid_0">Category *</label>
                        <select class="form-control" id="prod_catid_0" name="catid">
                </select>
                    </div>
                    <div class="form-group">
                        <label for="prod_name">Name *</label>
                        <input class="form-control" id="prod_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" />
                    </div>
                    <div class="form-group">
                        <label for="prod_price">Price *</label>
                        <input class="form-control" id="prod_price" type="text" name="price" required="true" pattern="^[\d\.]+$" />
                    </div>
                    <div class="form-group">
                        <label for="prod_desc">Description</label>
                        <textarea class="form-control" id="prod_desc" rows="3" name="description"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Image *</label>
                        <input class="form-control-file" type="file" name="file" required="true" accept="image/jpeg" />
                    </div>

                    <input type="submit" value="Submit" class="btn btn-primary" />
                </fieldset>
            </form>

            <form class="myform myform_1" method="POST" action="admin-process.php?action=prod_delete" enctype="multipart/form-data">
                <fieldset>
                    <legend>Delete Product</legend>
                    <div class="form-group">
                        <label for="prod_catid_1">Category *</label>
                        <select class="form-control" id="prod_catid_1" name="catid">
                </select>
                    </div>
                    <div class="form-group">
                        <label for="prod_name">Name *</label>
                        <select class="form-control" id="prod_name_1" name="pid"></select>
                    </div>

                    <input type="submit" value="Submit" class="btn btn-primary" />
                </fieldset>
            </form>

            <form class="myform myform_2" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data">
                <fieldset>
                    <legend>Edit Product</legend>
                    <div class="form-group">
                        <label for="prod_catid">Category *</label>
                        <select class="form-control" id="prod_catid_2" name="catid">
                </select>
                    </div>
                    <div class="form-group">
                        <label for="prod_name">Name *</label>
                        <select class="form-control" id="prod_name_2" name="pid">
                </select>
                    </div>
                    <div class="form-group">
                        <label for="prod_new_name">New name of the product *</label>
                        <input class="form-control" id="prod_new_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" />
                    </div>
                    <div class="form-group">
                        <label for="prod_catid">New category of the product *</label>
                        <select class="form-control" id="prod_catid_2n" name="new_cat">
                </select>
                    </div>
                    <div class="form-group">
                        <label for="prod_price">Price *</label>
                        <input class="form-control" id="prod_price" type="text" name="price" required="true" pattern="^[\d\.]+$" />
                    </div>
                    <div class="form-group">
                        <label for="prod_desc">Description</label>
                        <textarea class="form-control" id="prod_desc" rows="3" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Image *</label>
                        <input class="form-control-file" type="file" name="file" required="true" accept="image/jpeg" />
                    </div>

                    <input type="submit" value="Submit" class="btn btn-primary" />
                </fieldset>
            </form>

            <form class="myform myform_3" method="POST" action="admin-process.php?action=cat_insert">
                <fieldset>
                    <legend>Add Category</legend>
                    <div class="form-group">
                        <label for="prod_catid">Category *</label>
                        <input class="form-control" id="prod_catid_3" type="text" name="cat_name" required="true" pattern="^[\w\- ]+$" />
                    </div>

                    <input type="submit" value="Submit" class="btn btn-primary" />
                </fieldset>
            </form>

            <form class="myform myform_4" method="POST" action="admin-process.php?action=cat_delete">
                <fieldset>
                    <legend>Delete Category</legend>
                    <div class="form-group">
                        <label for="prod_catid_4">Category *</label>
                        <select class="form-control" id="prod_catid_4" name="catid">
                </select>
                    </div>

                    <input type="submit" value="Submit" class="btn btn-primary" />
                </fieldset>
            </form>

            <form class="myform myform_5" method="POST" action="admin-process.php?action=cat_edit">
                <fieldset>
                    <legend>Edit Category *</legend>
                    <div class="form-group">
                        <label for="prod_catid_5">Category *</label>
                        <select class="form-control" id="prod_catid_5" name="catid">
                </select>
                    </div>

                    <div class="form-group">
                        <label for="cat_new_name">New name of category *</label>
                        <input class="form-control" id="cat_new_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" />
                    </div>

                    <input type="submit" value="Submit" class="btn btn-primary" />
                </fieldset>
            </form>
        </div>


        <ul id="purchased-list">
            <p>Purchased List: </p>
            <?php
                $db = new PDO('sqlite:/var/www/cart.db');
                $q = $db->query("SELECT oid FROM orders ORDER BY oid DESC LIMIT 10");
                $oids = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                $q = $db->query("SELECT username FROM orders ORDER BY oid DESC LIMIT 10");
                $users = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                $q = $db->query("SELECT createdtime FROM orders ORDER BY oid DESC LIMIT 10");
                $createdtimes = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                $q = $db->query("SELECT status FROM orders ORDER BY oid DESC LIMIT 10");
                $status = $q->fetchAll(PDO::FETCH_COLUMN, 0);

    $totalIncome = 0.0;
    for ($i = 0, $len = count($oids); $i < $len; $i++) {
        echo "<li>OrderID:".$oids[$i]."&emsp;"."Username:&nbsp;".$users[$i]."&emsp;".$createdtimes[$i]."&emsp;".$status[$i]."</li>";
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
    }
 //   echo "<p id='totalIncome'>Total Income: ".$totalIncome."HKD</p>";
    ?>
        </ul>


        <!-- Footer -->

        <footer class="py-4 bg-dark footer">
            <div class="container">
                <p class="m-0 text-center text-white">Copyright &copy; KavinYou_2017</p>
            </div>
        </footer>

        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/popper/popper.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/myLib.js"></script>
        <script type="text/javascript" src="js/admin.js"></script>
    </body>

    </html>
