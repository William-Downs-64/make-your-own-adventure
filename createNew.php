<?php
session_start();

// Create connection
$conn = new mysqli("localhost", "root", "", "cyoa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(array_key_exists('logOut', $_POST)) {
    session_unset();
    
    setcookie("id", "", time() - 60 * 60);

    echo "logging Out";
    // header('Location: index.php');
}

$username = "anonymous";
if (array_key_exists('user', $_SESSION)) {
    $username = $_SESSION['user'];
    echo "Welcome: $username";
    echo '<form method="post" id="logout" class="float-end">
                <input type="submit" name="logOut" value="logout" class="btn btn-outline-primary">
            </form>';
} else {
    echo "Not Logged in!";
    echo '<div id="login" class="float-end">
            <a type="button" href="/login" class="btn btn-outline-primary">Login</a>
        </div>';
}

if (array_key_exists('scenario', $_GET)) {
    $_SESSION['scenario'] = $_GET['scenario'];
}

if (array_key_exists('newSubmit', $_POST) && array_key_exists('newTable', $_POST)) {
    $newTable = $_POST['newTable'];
    $query = "SELECT * FROM tables WHERE name = '$newTable'";
    // echo $_POST['public'];
    if (array_key_exists('public', $_POST) && $_POST['public']) {
        $public = '-all';
    } else {
        $public = '-none';
    }

    $result = mysqli_query($conn, $query);

    if($row = mysqli_fetch_array($result) || $newTable == "tables" || $newTable == "users") {
        echo "That is already taken!";
    } else {

        echo "<br>Creating new table, please wait...<br>";
        $sql = "INSERT INTO `tables` (`id`, `name`, `creator`, `editor`, `viewer`) VALUES (NULL, '$newTable', '$username', '$public', '$public');";
        echo $sql;

        mysqli_query($conn, $sql);

        $createSql = "CREATE TABLE $newTable (
                    id int NOT NULL,
                    area varChar(255) NOT NULL,
                    choice1 varChar(255),
                    link1 int,
                    choice2 varChar(255),
                    link2 int,
                    author varChar(255),
                    PRIMARY KEY (id)
        )";

        echo $createSql;

        mysqli_query($conn, $createSql);
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <script type="text/javascript" src="jquery-3.7.1.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
            rel="stylesheet" 
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
            crossorigin="anonymous">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>

    <style type="text/css">
        :root {
            --debug: none;
        }
        
        .currentChoice {
            margin-top: 10px;
            width: 80%;
            background-color: whitesmoke;
            padding: 10px;
        }

        .history-container {
            height: 200px;
            overflow-y:scroll;
            position: relative;
        }
        .history-container thead {
            position:sticky;
            top: 0;
            background-color: whitesmoke;
        }

        .link.active {
            background-color: yellow;
        }

        .debug {
            display: var(--debug);
        }
        #debug-toggle {
            border: none;
            opacity: 0;
            margin-left: 250px;
            width: 25px;
            cursor: help;
        }
  </style>

</head>
<body>

    <form>
        <select name="table" id="tableSelector" class="btn btn-dark">
            <?php
                $result = mysqli_query($conn, "show tables");
                while($tablename = mysqli_fetch_array($result)) {        
                    echo "<option>" . $tablename[0] . "</option>";
                }
            ?>
        </select>
        <input type="button" id="restart" value="Select" class="btn btn-outline-dark restart">
        <input type="checkbox" id="debug-toggle">
    </form>



    <br>
    <div class="bg-secondary">
        <form method="post">
            <input type="text" name="newTable">
            <label for="public">Public?</label>
            <input type="checkbox" name="public">
            <input type="submit" name="newSubmit">
        </form>
    </div>

<script>

    var id = 0;
    var object = "";
    let debug = false;
    let table = "portal";

    <?php if (array_key_exists("scenario", $_SESSION)) {
        echo "table = '" . $_SESSION['scenario'] . "';";
        }
        
        echo "let username = '$username';"; 
    ?>

    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }



</script>

</body>
</html>