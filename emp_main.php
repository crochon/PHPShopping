<html>
    <body>
        <?php
        session_start();
        require "db.php";
        if (!isset($_SESSION["empUsername"])){
            header("LOCATION:login.php");
        }
        ?>
        <h1> Welcome <?php echo $_SESSION["empUsername"]; ?> </h1>
        <form method = "post" action = "emp_main.php">
            <p> <input type = "submit" value = "Stock Changing History" name = "stockChange">
            <input type = "submit" value = "Price History" name = "priceHistory">
            <input type = "submit" value = "Restock a Product" name = "restock">
            <input type = "submit" value = "Change Price of a Product" name = "changePrice">
            <input type = "submit" value = "Logout" name = "logout"> </p>
        </form>

        <?php
            if(isset($_POST["logout"])){
                ?>
                <form method = "post" action = "emp_main.php">
                    <p> You are currently logged in. Would you like to logout </p>
                    <input type = "submit" value = "Logout" name = "confirmLogout">
                </form>
                <?php
            } 
            
            if(isset($_POST["confirmLogout"])){
                session_destroy();
                header("LOCATION:login.php");
            }

            if(isset($_POST["stockChange"])){
                ?>
                <form method = "post" action = "emp_main.php">
                    <p> Enter the product id: <input type = "number" value = "0" min = "0" name = productID></p>
                    
                </form>
                <?php
            }

            if(isset($_POST["productID"])){
                ?>
                <table border="1">
                    <tr>
                        <th>Modification Timestamp</th>
                        <th>Old Stock</th>
                        <th>New Stock</th>
                        <th>Changes</th>
                    </tr>
                <?php
                $result = changed_stocks($_POST["productID"]);

                foreach ($result as $row) {
                    //print_r($row);
                    $changes = $row["newStock"] - $row["oldStock"];
                    if ($changes != 0) {
                        echo "<tr>";
                        echo "<td>" . $row["time"] . '</td>';
                        echo "<td>" . $row["oldStock"] . '</td>';
                        echo "<td>" . $row["newStock"] . '</td>';
                        echo "<td>" . $changes . '</td>';
                        echo "</tr>";
                    }
                }
                echo "</table>";
            }

            if(isset($_POST["priceHistory"])){
                ?>
                <form method = "post" action = "emp_main.php">
                    <p> Enter the product id: <input type = "number" value = "0" min = "0" name = productID1></p>
                    
                </form>
                <?php
            }

            if(isset($_POST["productID1"])){
                ?>
                <table border="1">
                    <tr>
                        <th>Modification Timestamp</th>
                        <th>Old Price</th>
                        <th>New Price</th>
                        <th>Percentage</th>
                    </tr>
                <?php
                $result = changed_prices($_POST["productID1"]);

                foreach ($result as $row) {
                    //print_r($row);
                    if($row["oldPrice"] != null){
                        $percentage = round((($row["newPrice"] - $row["oldPrice"]) / $row["oldPrice"]) * 100, 2) . '%';
                    }
                    else{
                        $percentage = null;
                    }
                    
                    echo "<tr>";
                    echo "<td>" . $row["time"] . '</td>';
                    echo "<td>" . $row["oldPrice"] . '</td>';
                    echo "<td>" . $row["newPrice"] . '</td>';
                    echo "<td>" . $percentage . '</td>';
                    echo "</tr>";
                }
                echo "</table>";
            }

            if(isset($_POST["restock"])){
                ?>
                <form method = "post" action = "emp_main.php">
                    <p> Enter the product id: <input type = "number" value = "0" min = "0" name = productID2></p>
                    <p> Enter the Quantity: <input type = "number" value = "0" min = "0" name = quantity></p>
                    <input type = "submit" value = "Restock" name = "submitRestock">
                </form>
                <?php
            }

            if(isset($_POST["submitRestock"])){
                restock_product($_POST["productID2"], $_POST["quantity"]);
            }

            if(isset($_POST["changePrice"])){
                ?>
                <form method = "post" action = "emp_main.php">
                    <p> Enter the product id: <input type = "number" value = "0" min = "0" name = productID3></p>
                    <p> Enter the Price: <input type = "number" value = "0" min = "0" name = price></p>
                    <input type = "submit" value = "Price Change" name = "changed">
                </form>
                <?php
            }

            if(isset($_POST["changed"])){
                change_price($_POST["productID3"], $_POST["price"]);
            }
        ?>
    </body>
</html>
