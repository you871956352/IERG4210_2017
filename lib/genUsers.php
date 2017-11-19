<?php
    include_once('db.inc.php');
    global $db;
    $db = ierg4210_DB();

    //Create salted & hashed password
    function hash_pw($password){
        $salt = mt_rand();
        $hash = hash_hmac('sha1', $password, $salt);
        $a = array($salt,$hash);
        return $a;
    }
    $admin = "admin@gmail.com";
    $adminPwd = "admin";
    $hash_pw0 = hash_pw($adminPwd);

    $user1 = "guest@gmail.com";
    $pswd1 = "guest";
    $hash_pw1 = hash_pw($pswd1);

    try {
        $q = $db->prepare("INSERT INTO account (email,salt,password) VALUES (?,?,?)");
        $q->bindParam(1,$admin);
        $q->bindParam(2,$hash_pw0[0]);
        $q->bindParam(3,$hash_pw0[1]);
        $q->execute();

        $q->bindParam(1,$user1);
        $q->bindParam(2,$hash_pw1[0]);
        $q->bindParam(3,$hash_pw1[1]);
        $q->execute();

        echo "<p>Generated successfully</p>";
    }
    catch (Exception $e){
        echo $e->getMessage();
        exit();
    }
?>
