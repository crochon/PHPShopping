<?php
    function connectDB() {
        $config = parse_ini_file("/local/my_web_files/crrochon/db.ini");
        $dbh = new PDO($config['dsn'], $config['username'], $config['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

    function register_customer($user, $passwd, $fName, $lName, $email, $address) {
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("call create_customer(:username, :password, :firstName, :lastName, :email, :address) ");
            $statement->bindParam(":username", $user);
            $statement->bindParam(":password", $passwd);
            $statement->bindParam(":firstName", $fName);
            $statement->bindParam(":lastName", $lName);
            $statement->bindParam(":email", $email);
            $statement->bindParam(":address", $address);
            $result = $statement->execute();
            $rowCount = $statement->rowCount();
            return $rowCount;
        } catch(PDOException $e){
            echo 'User already exists';
        }
    }

    function authenticate_customer($user, $passwd){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT count(*) FROM customer WHERE username = :username and password = sha2(:passwd, 256) ");
            $statement->bindParam(":username", $user);
            $statement->bindParam(":passwd", $passwd);
            $result = $statement->execute();
            $row = $statement->fetch();
            $dbh = null;

            return $row[0];
        } catch (PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function customer_password_change($user, $oldPasswd, $newPasswd){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("call change_customer_password(:username, :oldPass, :newPass) ");
            $statement->bindParam(":username", $user);
            $statement->bindParam(":oldPass", $oldPasswd);
            $statement->bindParam(":newPass", $newPasswd);
            $result = $statement->execute();
            $row = $statement->rowCount();
            $dbh = null;

            return $row;
        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function read_all_categories(){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT name FROM category");
            $statement->execute();
            $row = $statement->fetchAll(PDO::FETCH_COLUMN);

            $dbh = null;
            return $row;
        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

?>
