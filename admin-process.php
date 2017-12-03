<?php

include_once('lib/db.inc.php');
include_once('lib/csrf.php');

//Account authentication.
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

//Data process.

function ierg4210_prod_fetchByPid() {
    //DB manipulation
    global $db;
    $db = ierg4210_DB();
    $PID = $_GET['pID'];
    $q = $db->prepare("SELECT name,price FROM products WHERE pid = $PID");
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_cat_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_prod_fetchByCat() {
     //DB manipulation
    global $db;
    $db = ierg4210_DB();
    $catID = $_POST['catID'];
    $q = $db->prepare("SELECT * FROM products WHERE catid = $catID LIMIT 100");
    if ($q->execute())
       return $q->fetchAll();
}

function ierg4210_prod_insert() {
    // input validation or sanitization
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    if (!preg_match('/^[\w\-,\.\' ]+$/', $_POST['description']))
        throw new Exception("invalid-description");

    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    $q = $db->prepare("INSERT INTO products (catid, name, price, description) VALUES (?,?,?,?)");
    $q->execute(array($_POST['catid'],$_POST['name'],$_POST['price'],$_POST['description']));

    // The lastInsertId() function returns the pid (primary key) resulted by the last INSERT command
    $lastId = $db->lastInsertId();

    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
    if ($_FILES["file"]["error"] == 0
        && ($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/gif")
        && $_FILES["file"]["size"] < 5000000) {

        // Note: Take care of the permission of destination folder (hints: current user is apache)
        if (move_uploaded_file($_FILES["file"]["tmp_name"], "img/" . $lastId . ".jpg")) {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Insert succeeded. <br/>Product name : ' . $_POST['name'] . '<br/><a href="admin.php">Back to admin panel.</a>';
            exit();
        }
    }
    header('Content-Type: text/html; charset=utf-8');
    echo 'Invalid picture. <br/><a href="admin.php">Back to admin panel.</a>';
    exit();
}    

function ierg4210_prod_edit() {
    // input validation or sanitization
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    if (!preg_match('/^[\w\-,\.\' ]+$/', $_POST['description']))
        throw new Exception("invalid-description");

    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    $pid  = (int)$_POST["pid"];
    $q = $db->prepare("UPDATE products SET catid=(?),name=(?),price=(?),description=(?) WHERE pid=$pid");
    $q->execute(array($_POST['new_cat'],$_POST['name'],$_POST['price'],$_POST['description']));

    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
    if ($_FILES["file"]["error"] == 0
        && ($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/gif")
        && $_FILES["file"]["size"] < 5000000) {

        // Note: Take care of the permission of destination folder (hints: current user is apache)
        if (move_uploaded_file($_FILES["file"]["tmp_name"], "img/" . $pid . ".jpg")) {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Edit succeeded. <br/>Product name : ' . $_POST['name'] . '<br/><a href="admin.php">Back to admin panel.</a>';
            exit();
        }
    }
    
    // Only an invalid file will result in the execution below
    // TODO: remove the SQL record that was just inserted

    // To replace the content-type header which was json and output an error message
    header('Content-Type: text/html; charset=utf-8');
    echo 'Invalid picture. <br/><a href="admin.php">Back to admin panel.</a>';
    exit();
}

function ierg4210_prod_delete() {
    // input validation or sanitization
    $_POST['pid'] = (int) $_POST['pid'];

    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("DELETE FROM products WHERE pid = ?");
    $q->execute(array($_POST['pid']));
    
    header('Content-Type: text/html; charset=utf-8');
    echo 'Deletion succeeded. <br/><a href="admin.php">Back to admin panel.</a>';
    exit();
}


function ierg4210_cat_insert() {
	// input validation or sanitization
	if (!preg_match('/^[\w\- ]+$/', $_POST['cat_name']))
		throw new Exception("invalid-name");

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO categories (name) VALUES (?)");
	$q->execute(array($_POST['cat_name']));
    
    header('Content-Type: text/html; charset=utf-8');
    echo 'Insert succeeded. <br/><a href="admin.php">Back to admin panel.</a>';
    exit();
}


function ierg4210_cat_delete() {

	// input validation or sanitization
	$_POST['catid'] = (int) $_POST['catid'];

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
    
	//$q = $db->prepare("DELETE FROM categories WHERE catid = ?");
	//$q->execute(array($_POST['catid']));
    
	$q2 = $db->prepare("DELETE FROM products WHERE catid = ?");
	$q2->execute(array($_POST['catid']));
    
	$q = $db->prepare("DELETE FROM categories WHERE catid = ?");
	$q->execute(array($_POST['catid']));
     
    header('Content-Type: text/html; charset=utf-8');
    echo 'Deletion succeeded. <br/><a href="admin.php">Back to admin panel.</a>';
    exit();
}


function ierg4210_cat_edit() {

    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    $catID = (int) $_POST['catid'];

    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("UPDATE categories SET name = (?) WHERE catid = $catID");
    $q->execute(array($_POST['name']));
    
    header('Content-Type: text/html; charset=utf-8');
    echo 'Edit succeeded. <br/><a href="admin.php">Back to admin panel.</a>';
    exit();
}


// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}

// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
// the return values of the functions are then encoded in JSON format and used as output
try {
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>
