<html>
    <body>
        
        <form method = "post" action = "login.php">
            <p align = "right">
            <input type = "submit" value = "Employee Login" name = "empLogin">
        </form>

        <form method = "post" action = "login.php">
        <p>Username: <input type="text" name="username"><br></p>
        <p>Password: <input type="password" name="password"><br><br></p>
        <p> <input type="submit" value="Login" name="login"> 
            <input type="submit" value="Register" name="register"> </p>
        </form>

        <?php
            require "db.php";
            session_start();
            if (isset($_POST["login"])){
                if(authenticate_customer($_POST["username"], $_POST["password"]) == 1) {
                    $_SESSION["username"]=$_POST["username"];
                    header("LOCATION:cust_main.php");
                    return;
                }
                else{
                    echo '<p style = "color:red"> incorrect username and password</p>';
                }
            }
            else if (isset($_POST["register"])){
                ?>
                <form method = "post" action = "login.php">
                    <p>Username: <input type="text" name="username"><br></p>
                    <p>Password: <input type="password" name="password"><br></p>
                    <p>Password Again: <input type="password" name="password_again"><br></p>
                    <p>First Name: <input type="text" name="firstName"><br></p>
                    <p>Last Name: <input type="text" name="lastName"><br></p>
                    <p>Email: <input type="text" name="email"><br></p>
                    <p>Shipping Address: <input type="text" name="shippingAddress"><br></p>
                    <input type="submit" value="Register" name="createUser"> 
                    <input type ="submit" value="Cancel" name="cancelUser"></p>
                    
                </form>
                <?php
            }
            if(isset($_POST["createUser"])){
                if($_POST["password"] == $_POST["password_again"]){
                    $result =register_customer($_POST["username"], $_POST["password"], $_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["shippingAddress"]);
                    if($result == 1){
                        echo 'Customer record created sucessfully';
                    }
                }
                else{
                    echo 'Password does not match';
                }
            }

            if(isset($_POST['empLogin'])){
                ?>
                <form method = "post" action = "login.php">
                    <p>Username: <input type="text" name="empUsername"><br></p>
                    <p>Password: <input type="password" name= "empLogin"><br></p>
                    <input type="submit" value="Login" name="empSubmit"> 
                </form>
                <?php
            }

            if(isset($_POST['empSubmit'])){
                if(emp_login($_POST["empUsername"], $_POST["empLogin"])== 1){
                    $_SESSION['empUsername'] = $_POST["empUsername"];
                    header("LOCATION:emp_main.php");
                }
            }

            if(isset($_POST["updatePass"])){
                if($_POST["newPass"] == $_POST["newPass"]){
                    emp_update_password($_POST["empUsername"], $_POST["newPass"]);
                }
            }
            
        ?>
    </body>
</html>