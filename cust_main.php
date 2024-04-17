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
                    <input type = "submit" value = "Shopping Cart" name = "shoppingCart">
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

            /*
            Runs once the User hits the search button (not logged in)
            */
            if(isset($_POST['search']) && !isset($_SESSION["username"])){
                $result = select_product_from_category($_POST["categories"]);
                foreach ($result as $row) {
                    echo '<h1>' . $row["name"] . '</h1>';
                    echo '<img src =' . $row["image"] . ' width = "500" height = "500"> Price: $' . $row["price"];
                }
            }

            /*
            Runs once the User hits the search button (logged in)
            */
            if(isset($_POST['search']) && isset($_SESSION["username"])){
                $result = select_product_from_category($_POST["categories"]);
                foreach ($result as $row) {
                    echo '<h1>' . $row["name"] . '</h1>';
                    echo '<img src =' . $row["image"] . ' width = "500" height = "500"> Price: $' . $row["price"];
                    ?>
                    <form method = "post" action = "cust_main.php">
                        <input type = "number" name = "quantity" min = "1">
                        <input type = "submit" value = "Add to Cart" name = "addCart">
                        <input type = "hidden" value = "<?php echo $row["productID"] ?>" name = "productID">
                    </form>
                    <?php
                }
            }

            /*
            Runs when the user tries to add an item to the cart
            */
            if(isset($_POST["addCart"])) {
                //print_r($_POST);
                //Call to the database to add item to cart
                add_to_cart($_POST["productID"], $_SESSION["username"], $_POST["quantity"]);
            }

            /*
            Runs when the user wants to view their shopping cart
            */
            if(isset($_POST["shoppingCart"])) {
                echo '<h1> Your Shopping Chart</h1>';
                ?>
                <table border="1">
                    <tr>
                        <th>Product Id</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                <?php
                $result = view_Cart($_SESSION["username"]);

                foreach ($result as $row) {
                    echo "<tr>";
                    echo '<form method = "post" action = "cust_main.php">';
                    echo "<td>" . $row["productID"] . '</td>';
                    echo "<td>" . $row["productName"] . '</td>';
                    echo "<td>" . $row["price"] . '</td>';
                    echo "<td>" . '<input type = "number" name = "cartQuantity" value="'.$row["quantity"].'" min = "1">' . '</td>';
                    echo '<td> 
                                <input type = "submit" value = "Update" name = "update">
                                <input type = "submit" value = "Remove" name = "remove"> 
                                <input type = "hidden" value = "'.$row["productID"] .'" name = "cartProductID">
                                </td>';
                                echo '</form>';
                    echo "</tr>";
                }
                echo "</table>";

                ?>
                <form method = "post" action = "cust_main.php">
                    <input type = "submit" value = "Checkout" name = "checkout">
                </form>
                <?php
            }

            /*
            Runs when the user wants to update an item in their shopping cart
            */
            if(isset($_POST["update"])) {
                update_cart($_POST["cartProductID"], $_SESSION["username"], $_POST["cartQuantity"]);    
            }

            /*
            Runs when the user wnats to remove an item in their shopping cart
            */
            if(isset($_POST["remove"])) {
                remove_from_cart($_POST["cartProductID"], $_SESSION["username"]);
            }

            /*
            Runs when the user wants to checkout their shopping cart
            */
            if(isset($_POST["checkout"])) {
                $result = view_Cart($_SESSION["username"]);
                if(count($result) != 0) {
                    checkout($_SESSION["username"]);
                }
                else{
                    echo 'Your shopping cart is currently empty!';
                }
            }

            /*
            Runs when the user wants to view their previous orders
            */
            if(isset($_POST["viewOrders"])) {
                echo '<h1> Here are your orders:</h1>';
                ?>
                <ol>
                <?php
                $result = view_orders($_SESSION["username"]);
                foreach ($result as $row) {
                    echo '<li>';
                    echo '<p> Order id: ' . $row['orderID'] . '</p>';
                    echo '<p> Order time: ' .$row['order_date'] . '</p>';
                    echo '<p> Total amount: ' . $row['total_amount'] . '</p>';

                    $ordersResult = view_order_items($row['orderID']);
                    ?>
                    <table border="1">
                        <tr>
                            <th>Product Id</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                    <?php
                    foreach ($ordersResult as $orderRow) {
                        echo "<tr>";
                        echo "<td>" . $orderRow["productID"] . '</td>';
                        echo "<td>" . $orderRow["productName"] . '</td>';
                        echo "<td>" . $orderRow["price"] . '</td>';
                        echo "<td>" . $orderRow["quantity"] . '</td>';
                        echo "</tr>";
                    }
                    echo '</table>';
                    echo '</li>';
                }
                echo '</ol>';
            }
        ?>
    </body>
</html>