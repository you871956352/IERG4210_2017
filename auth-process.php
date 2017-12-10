<?php
    define("LOG_FILE", "/var/www/auth.log");
    session_start();
    include_once('lib/db.inc.php');
    global $db;
    $db = ierg4210_DB();

    function ierg4210_signup(){
        //echo "<script>alert(\"ds\")</script>";
        if (empty($_POST['email']) || empty($_POST['pw'])
            || !preg_match('/^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$/', $_POST['email'])
            || !preg_match('/^[A-Za-z_\d]{2,19}$/', $_POST['pw']))
            throw new Exception('Wrong Credentials');

        // Implement the login logic here
        else {
            global $db;
            $db = ierg4210_DB();
            $email = $_POST['email'];
            $password = $_POST['pw'];
            $hash_pw = hash_pw($password);
            
            
            try {
                $q = $db->prepare("INSERT INTO account (email,salt,password) VALUES (?,?,?)");
                $q->bindParam(1,$email);
                $q->bindParam(2,$hash_pw[0]);
                $q->bindParam(3,$hash_pw[1]);
                $q->execute();
                
                header('Content-Type: text/html; charset=utf-8');
                echo 'Signup success. <br/><a href="login.php">Back to login page.</a>';
                //echo 'email'.$email.'salt'.$hash_pw[0].'password'.$hash_pw[1];
                exit();
            }catch (Exception $e){
                echo $e->getMessage();
                exit();
            }
        }
    }

    function ierg4210_login(){
        //echo "<script>alert(\"ds\")</script>";
        if (empty($_POST['email']) || empty($_POST['pw'])
            || !preg_match('/^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$/', $_POST['email'])
            || !preg_match('/^[A-Za-z_\d]{2,19}$/', $_POST['pw']))
            throw new Exception('Wrong Credentials');

        // Implement the login logic here
        else {
            global $db;
            $db = ierg4210_DB();
            $email = $_POST['email'];
            $q = $db->prepare("SELECT * FROM account WHERE email = ?");
            $q->execute(array($email));
            $r = $q->fetch();
            if (empty($r)) {
                header('Location:login.php', true, 302);
                throw new Exception('Wrong users');
            }
            else {
                $salt = $r['salt'];
                $savedPwd = $r['password'];
                $sh_pwd = hash_hmac('sha1', $_POST['pw'], $salt);
                if ($savedPwd == $sh_pwd) {
                    session_regenerate_id();
                    $exp = time()+3600*24*3;
                    $token = array(
                        'em'=>$email,
                        'exp'=>$exp,
                        'k'=>hash_hmac('sha1', $exp.$savedPwd, $salt)
                    );
                    //create cookie, make it HTTP only
                    //setcookie() must be called before printing anything out
                    setcookie('t4210',json_encode($token),$exp,'','',false,true);
                    $_SESSION['t4210'] = $token;

                    if ($email == "admin@gmail.com") {
                        header('Location: admin.php', true, 302);
                        exit();
                    }
                    else {
                        header('Location: index.php', true, 302);
                        exit();
                    }
                }
                else {
                    header('Location:login.php', true, 302);
                    throw new Exception('Wrong password');
                }
            }
        }
    }

    function ierg4210_logout(){
        // clear the cookies and session
        if (isset($_COOKIE['t4210'])) {
            unset($_COOKIE['t4210']);
            setcookie('t4210',null,-1);
            session_start();
            session_unset();
            session_destroy();
            // redirect to login page after logout
            header('Location:login.php', true, 302);
            exit();
        }
        else {
            header('Location:login.php', true, 302);
            exit();
        }
    }

    header("Content-type: text/html; charset=utf-8");
    try {
        // input validation
        if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action']))
            throw new Exception('Undefined Action');

        // check if the form request can present a valid nonce
        include_once('lib/csrf.php');
        csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);

        // run the corresponding function according to action
        if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
            if ($db && $db->errorCode())
                error_log(print_r($db->errorInfo(), true));
            throw new Exception('Failed');
        } else {
            // no functions are supposed to return anything
            // echo $returnVal;
        }
    }
    catch(PDOException $e) {
        error_log($e->getMessage());
        header('Refresh: 10; url=login.php?error=db');
        echo '<strong>Error Occurred:</strong> DB <br/>Redirecting to login page in 10 seconds...';
    }
    catch(Exception $e) {
        header('Refresh: 10; url=login.php?error=' . $e->getMessage());
        echo '<strong>Error Occurred:</strong> ' . $e->getMessage() . '<br/>Redirecting to login page in 10 seconds...';
    }

    function hash_pw($password){
        $salt = mt_rand();
        $hash = hash_hmac('sha1', $password, $salt);
        $a = array($salt,$hash);
        return $a;
    }

    function ierg4210_changePwd() {
        $email = $_POST['user_email'];
        $old_pw = $_POST['old_pw'];
        $new_pw = $_POST['new_pw'];
        $r_new_pw = $_POST['r_new_pw'];

        error_log(date("Y-m-d H:i:s"). "email:" .$email.";old".$old_pw.";new1:".$new_pw.";r_new:".$r_new_pw.PHP_EOL, 3, LOG_FILE);

        if (empty($email) || empty($old_pw) || empty($new_pw) || empty($r_new_pw)
            || !preg_match('/^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$/', $email)
            || !preg_match('/^[A-Za-z_\d]{2,19}$/', $old_pw) || !preg_match('/^[A-Za-z_\d]{2,19}$/', $new_pw)
            || !preg_match('/^[A-Za-z_\d]{2,19}$/', $r_new_pw))
        {
            header('Location:change_pwd.php', true, 302);
//            throw new Exception('Wrong Credentials');
            error_log(date("Y-m-d H:i:s"). "Wrong Credentials" . PHP_EOL, 3, LOG_FILE);
        }
        else if ($new_pw != $r_new_pw)
        {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Two new passwords are different. <br/><a href="change_pwd.php">Back to password change page.</a>';
            
//            throw new Exception('Two new passwords are different');
            error_log(date("Y-m-d H:i:s"). "Two new passwords are different" . PHP_EOL, 3, LOG_FILE);
        }
        else {
            global $db;
            $db = ierg4210_DB();

            $q = $db->prepare("SELECT * FROM account WHERE email = ?");
            $q->execute(array($email));
            $r = $q->fetch();
            if (empty($r)) { // wrong email
                header('Content-Type: text/html; charset=utf-8');
                echo 'Wrong account! <br/><a href="change_pwd.php">Back to password change page.</a>';
                
                error_log(date("Y-m-d H:i:s"). "Wrong Account!" . PHP_EOL, 3, LOG_FILE);
//                throw new Exception('Wrong Account!');
            }
            else { // email exists
                $salt = $r['salt'];
                $savedPwd = $r['password'];
                $sh_pwd = hash_hmac('sha1', $old_pw, $salt);
                if ($savedPwd == $sh_pwd) { //true old password
                    $new_salt = mt_rand();
                    $sh_new_pwd = hash_hmac('sha1', $new_pw, $new_salt);

                    $q = $db->prepare("UPDATE account SET password=?, salt=? WHERE email = ?");
                    $q->execute(array($sh_new_pwd, $new_salt, $email));
                    ierg4210_logout();
                }
                else {
                    header('Content-Type: text/html; charset=utf-8');
                    echo 'Wrong password! <br/><a href="change_pwd.php">Back to password change page.</a>';
                    
                    error_log(date("Y-m-d H:i:s"). "Wrong password!" . PHP_EOL, 3, LOG_FILE);
                    //throw new Exception('Wrong password');
                }
            }
        }
    }
?>
