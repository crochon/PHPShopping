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

    function authenticate_employee($user, $passwd, $dbh){
        try{
            $statement = $dbh->prepare("SELECT count(*) FROM employee WHERE username = :username and password = sha2(:passwd, 256) ");
            $statement->bindParam(":username", $user);
            $statement->bindParam(":passwd", $passwd);
            $result = $statement->execute();
            $row = $statement->fetch();

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

    function select_product_from_category($category){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT * FROM product WHERE category_name = :category");
            $statement->bindParam(":category", $category);
            $statement->execute();
            $row = $statement->fetchAll(PDO::FETCH_ASSOC);

            $dbh = null;
            return $row;
        }
        catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function add_to_cart($productID, $username, $quantity){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("call add_to_cart(:productID, :customerUsername, :quantity) ");
            $statement->bindParam(":productID", $productID);
            $statement->bindParam(":customerUsername", $username);
            $statement->bindParam(":quantity", $quantity);
            $statement->execute();

            $dbh = null;
            echo 'Added the product id ' . $productID .' to cart sucessfully!';

        } catch(PDOException $e) { 
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function view_Cart($username){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT productID, productName, price, quantity FROM shopping_cart WHERE customerID = (SELECT customerID from customer where username = :username)");
            $statement->bindParam(":username", $username);
            $statement->execute();

            $row = $statement->fetchAll(PDO::FETCH_ASSOC);
            $dbh = null;
            return $row;
        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function remove_from_cart($productID, $username){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("DELETE FROM shopping_cart WHERE productID = :productID AND customerID = (SELECT customerID from customer where username = :username)");
            $statement->bindParam(":productID", $productID);
            $statement->bindParam(":username", $username);
            $statement->execute();

            $dbh = null;
            echo 'The item has been removed!';

        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function update_cart($productID, $username, $newQuantity){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("UPDATE shopping_cart SET quantity = :newQuantity WHERE productID = :productID AND customerID = (SELECT customerID from customer where username = :username)");
            $statement->bindParam(":productID", $productID);
            $statement->bindParam(":username", $username);
            $statement->bindParam(":newQuantity", $newQuantity);
            $statement->execute();

            $dbh = null;
            echo 'The quantity has been updated';

        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function checkout($username){
        try{
            //Creating the order
            $dbh = connectDB();
            $dbh-> beginTransaction();
            $statement = $dbh->prepare("set @order = insert_order((SELECT customerID FROM customer WHERE username = :username))");
            $statement->bindParam(":username", $username);
            $statement->execute();
            $statement = $dbh->prepare("SELECT @order");
            $statement->execute();
            $result = $statement->fetch();
            $orderID = $result["@order"];

            //Checking out each item
            $result = view_Cart($username);
            foreach ($result as $row){
                // check quantity against shopping cart
                $statement = $dbh->prepare("Select quantity from product where productID = :productID");
                $statement->bindParam(":productID", $row["productID"]);
                $statement->execute();
                $productQuantity = $statement->fetch()['quantity'];

                if($productQuantity < $row['quantity']){
                    echo 'There are only '. $productQuantity .' left for product id '. $row['productID'] .'. Please update your cart';
                    $dbh->rollBack();
                    return;
                }

                $statement = $dbh->prepare("set @quantity = insert_order_item(:orderID, :productID, (SELECT quantity FROM shopping_cart WHERE productID = :productID AND CustomerID = (SELECT customerID FROM customer WHERE username = :username))) ");
                $statement->bindParam(":username", $username);
                //echo $row["customerID"];
                $statement->bindParam(":productID", $row["productID"]);
                $statement->bindParam(":orderID", $orderID);
                $statement->execute();

                $statement = $dbh->prepare("DELETE FROM shopping_cart WHERE productID = :productID AND customerID = (SELECT customerID from customer where username = :username)");
                $statement->bindParam(":productID", $row["productID"]);
                $statement->bindParam(":username", $username);
                $statement->execute();
            }

            $dbh->commit();
            $dbh = null;
            echo 'Order placed successfully';

        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }

    }

    function view_orders($username) {
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT orderID, total_amount, order_date FROM order_info WHERE customerID = (SELECT customerID from customer where username = :username) ");
            $statement->bindParam(":username", $username);
            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $dbh = null;
            return $result;


        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }

    }

    function view_order_items($orderID){
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT productID, productName, price, quantity FROM order_items WHERE orderID = :orderID");
            $statement->bindParam(":orderID", $orderID);
            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $dbh = null;
            return $result;

        }
        catch (PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function emp_login($username, $password){
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT password FROM employee WHERE username = :username");
            $statement->bindParam(":username", $username);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);


            $statement = $dbh->prepare("SELECT sha2('1234', 256) pwd");
            $statement->execute();
            $passRef = $statement->fetch(PDO::FETCH_ASSOC);
            //print_r($passRef);
            //print_r($result);
            
            foreach( $result as $row ){
                if($row['password'] == $passRef["pwd"]){ 
                echo'<form method = "post" action = "login.php">
                    <p>new password: <input type="password" name="newPass"><br></p>
                    <p>new password: <input type="password" name="newPassAgain"><br></p>
                    <input type = "hidden" value = "' . $username . '" name = "empUsername"> 
                    <input type = "submit" value = "Update Password" name = "updatePass">
                    </form>';
                }
                else{
                   if(authenticate_employee($username, $password, $dbh) == 1){
                        return 1;
                   }
                   else{
                    echo 'incorrect Password!';
                    return 0;
                   }
                }
            }
            return 0;

        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function emp_update_password($username, $password) {
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("update employee set password = sha2(:password, 256) where username = :username");
            $statement->bindParam(":username", $username);
            $statement->bindParam(":password", $password);
            $statement->execute();

            echo "New Password Set!";

        } catch (PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function changed_stocks($productID){
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT time, oldStock, newStock FROM product_history WHERE productID = :productID");
            $statement->bindParam(":productID", $productID);
            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            $dbh = null;
            //print_r($result);
            return $result;
            
        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function changed_prices($productID){
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT time, oldPrice, newPrice FROM product_history WHERE productID = :productID");
            $statement->bindParam(":productID", $productID);
            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            $dbh = null;
            //print_r($result);
            return $result;
            
        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function restock_product($productID, $quantity){
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("UPDATE product SET quantity = :quantity where productID = :productID");
            $statement->bindParam(":productID", $productID);
            $statement->bindParam(":quantity", $quantity);
            $statement->execute();
            
            $dbh = null;
            echo "Quantity of product " . $productID. " updated";
            
        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

    function change_price($productID, $price){
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("UPDATE product SET price = :price where productID = :productID");
            $statement->bindParam(":productID", $productID);
            $statement->bindParam(":price", $price);
            $statement->execute();
            
            $dbh = null;
            echo "Price of product " . $productID. " updated";
            
        } catch(PDOException $e) {
            print "Error!" . $e->getMessage() ."<br/>";
            die();
        }
    }

?>