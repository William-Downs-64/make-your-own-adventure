<?php
    session_start();

    if(!array_key_exists("user", $_SESSION)) {
        echo "Not logged in";
        header('Location: index.php');
    }

    if(array_key_exists("id", $_COOKIE)) {
        $_SESSION['user'] = $_COOKIE["id"];
    }

    if(array_key_exists('logOut', $_POST)) {
        session_unset();
        
        setcookie("id", "", time() - 60 * 60);

        echo "logging Out";
        header('Location: index.php');
    }

    include('connection.php');

    if (isset($_POST['donate'])){
        echo "<br><br><br><br>";
    if($_POST['code'] > 0) {

        $query = "SELECT * FROM login WHERE code = '"
        . $_POST['code'] . "'";

        if($result = mysqli_query($conn, $query)) {
            if($row = mysqli_fetch_array($result)) {
                
                echo $row['email'];

                echo "<br>";

                $string = "'" . $_POST["code"] . "' ,";
                $string .= "'" . $_POST["amount"] . "' ,";
                $string .= "'" . $_POST["frequency"] . "' ,";
                $string .= "'" . mysqli_real_escape_string($conn, $_SESSION["email"]) . "'";
                echo $string;
                $queryInsert = "INSERT INTO partners (code, amount, frequency, partner) VALUES (" . $string . ")";

                mysqli_query($conn, $queryInsert);
            }
            else{
                echo "That code could not be found.";
            }
        }
        else{
            echo "connection error";
        }

    }}

?>

<?php include("header.php"); ?>

        <div id="support">
            <h2 class="text-center">Support Missionaries!</h2>
            <form method="post">
                <label for="code">Rep Code: </label>
                <input type="number" id="code" name="code" class="form-control" required>
                
                <label for="amount">Money Amount:</label>
                <input type="number" id="amount" name="amount" class="form-control" required>
                
                <label for="frequency">Frequency:</label>
                <select name="frequency" class="form-select" id="frequency">
                    <option value="12">Monthly</option>
                    <option value="1">Yearly</option>
                    <option value="24">Bi-Monthly</option>
                    <option value="2">Six Months</option>
                    <option value="0">One Time Gift</option>
                </select>

                <input type="submit" class="btn btn-light form-control" name="donate">

                <!-- <input type="number" id="frequency" name="frequency">
                <label for="frequency">How many times a year?</label> -->
            </form>
        </div>

        <div>

            <?php
            if(!array_key_exists('code', $_SESSION)) {
                $_SESSION['code'] = 0;
            }
            if($_SESSION['code'] > 0) {

                echo '<h2 class="text-light">You are a Missionary</h2>';
                echo '<a class="btn btn-primary" href="partners.php">See partner list</a>';

            }
            else {
                echo '<h2 class="text-light">Become a Missionary</h2>';
                echo '<a class="btn btn-primary" href="join.php">Join the Mission!</a>';
            }
            ?>
        </div>
        

        <script type="text/javascript">

            if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
            }

        </script>
    </body>
</html>