<?php
    session_start();
    
    if(array_key_exists('logOut', $_POST)) {
        session_unset();
        
        setcookie("id", "", time() - 60 * 60);

        echo "logging Out";
        header('Location: index.php');
    }

    include("connection.php");

    $code = $_SESSION['code'];

    $records = mysqli_query($conn, "SELECT * FROM partners WHERE code='$code'");

    include("header.php")
?>


    <div id="support">
        <h1>Partner List</h1>
        <p>For: <?php echo $_SESSION['email'];?>
        <table class="table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Frequency</th>
                        <th>Partner</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    while($record = mysqli_fetch_array($records)) {
                    echo"<tr><td>$record[amount]</td><td>$record[frequency]</td><td>$record[partner]</td></tr>";
                    } ?>
                </tbody>
            </table>


    </div>
</body>
</html>