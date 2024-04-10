<html>
    <body>
        <?php
        session_start();
        require "db.php";
        if (!isset($_SESSION["username"])){
            ?>
            <form method = "post" action = login.php>
                <p><input type = "submit" value = "Login" name = "login</br>"></p>
            </form>
            <?php  
        }
        else{
            echo '<h1> Welcome ' . $_SESSION["username"] . '!! </h1>';
            ?>
            <form method = "post" action = "cust_main.php">
                <p> <input type = "submit" value = "View Orders" name = "viewOrders">
                    <input type = "submit" value = "Shopping Cart" name = "shoppingCCart">
                    <input type = "submit" value = "Change Password" name = "changePass">
                    <input type = "submit" value = "Logout" name = "logout"> </p>
            </form>
            <?php
        }
        ?>
        <form method = "post" action = cust_main.php>
            <?php
                //Stores the value into an array 
                $result = read_all_categories();
                ?>
                <select name = "categories" id = "categories">
                <?php
                foreach ($result as $row) {
                    echo '<option value = ' . $row . '>' . $row .' </option>' ;
                }
                ?>
                </select>  

            <input type = "submit" value = "Search" name = "search"></p>
        </form>

        <?php
            /*
            Lets the User logout of their account
            */  
            if(isset($_POST["logout"])){
                ?>
                <form method = "post" action = cust_main.php>
                    <p> You are currently logged in. Would you like to logout </p>
                    <input type = "submit" value = "Logout" name = "confirmLogout">
                </form>
                <?php
            }  

            /*
            Confirms the user's logout
            */
            if(isset($_POST["confirmLogout"])){
                session_destroy();
                header("LOCATION:cust_main.php");
            }

            /*
            Query for the User to change their password
            */
            if(isset($_POST["changePass"])){
                ?>
                <form method = "post" action = "cust_main.php">
                    <p>old password: <input type="password" name="oldPass"><br></p>
                    <p>new password: <input type="password" name="newPass"><br></p>
                    <p>new password: <input type="password" name="newPassAgain"><br></p>
                    <input type = "submit" value = "Update Password" name = "updatePass">
                </form>
                <?php
            }

            /*
            Changes the Users password
            */
            if(isset($_POST["updatePass"])){
                if($_POST["newPass"] == $_POST["newPassAgain"]){
                    $result = customer_password_change($_SESSION["username"], $_POST["oldPass"], $_POST["newPass"]);
                    if($_POST["oldPass"] == $_POST["newPass"]){
                        echo "New password is not different from old password";
                    }
                    else if($result == 0){
                        echo "Old password is not correct";
                    }
                }
                else{
                    echo 'New passwords are not matching';
                }
            }
        ?>
    </body>
</html>
