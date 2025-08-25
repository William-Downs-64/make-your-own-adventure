<?php
session_start();

// Create connection
$conn = new mysqli("localhost", "root", "", "cyoa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!array_key_exists('user', $_SESSION)) {
    header("location: new.php");
}

if (array_key_exists('user', $_SESSION)) {
    $user = $_SESSION['user'];
    echo "Welcome: $user";
    echo '<form method="post" id="logout" class="float-end">
                <a href="new.php" class="btn btn-secondary">Play</a>
                <input type="submit" name="logOut" value="logout" class="btn btn-outline-primary">
            </form>';
}

if (array_key_exists('newSubmit', $_POST) && array_key_exists('newTable', $_POST)) {
    $newTable = str_replace(" ", "_", $_POST['newTable']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $query = "SELECT * FROM tables WHERE name = '$newTable'";
    // echo $_POST['public'];
    $public2 = "-($user)";
    if (array_key_exists('viewPublic', $_POST) && $_POST['viewPublic']) {
        $public = '-all';
        if (array_key_exists('editPublic', $_POST) && $_POST['editPublic']) {
            $public2 = '-all';
        }
    } else {
        $public = "-($user)";
    }

    $result = mysqli_query($conn, $query);

    $error = "";
    $errorType = "danger";

    if($row = mysqli_fetch_array($result) || $newTable == "tables" || $newTable == "users") {
        $error = "That name is already taken";
    } elseif (preg_match('/[^A-Za-z0-9_]/', $newTable)) {
        $error = "Improper character detected! Letters and numbers only!";
    } else {

        echo $newTable;

        $extra = "";
        $type = $_POST['tableType'];

        if ($type == "Three" || $type == "RPG") {
            $extra = "choice3 varChar(255), link3 int NOT NULL,";
        }

        echo "<br>Creating new table, please wait...<br>";
        $sql = "INSERT INTO `tables` (`id`, `name`, `creator`, `editor`, `viewer`, `description`, `type`) VALUES (NULL, '$newTable', '$user', '$public', '$public2', '$description', '$type');";
        echo $sql;

        mysqli_query($conn, $sql);


        $createSql = "CREATE TABLE $newTable (
                    id int NOT NULL AUTO_INCREMENT,
                    area TEXT NOT NULL,
                    color varChar(7),
                    choice1 varChar(255),
                    link1 int NOT NULL,
                    choice2 varChar(255),
                    link2 int NOT NULL,
                    $extra
                    author varChar(255),
                    PRIMARY KEY (id)
        )";



        echo $createSql;

        mysqli_query($conn, $createSql);

        $update = "UPDATE `users` SET `myTables` = CONCAT(`myTables`, '-($newTable)') WHERE `username` = '$user'";
        mysqli_query($conn, $update);

        $_SESSION['scenario'] = $newTable;
        header("location: new.php");
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script type="text/javascript" src="jquery-3.7.1.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
            rel="stylesheet" 
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
            crossorigin="anonymous">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>

    <title>Choose Your Own Adventure</title>

    <link rel="stylesheet" href="style.css">

</head>
<body>

    <div id="errorHolder"></div>
        
    <form action="new.php" method="post">
        <label for="scenario">List of all Adventures</label>
        <select name="scenario" id="tableSelector" class="btn btn-dark">
            <?php
                $result = mysqli_query($conn, "show tables");
                while($tablename = mysqli_fetch_array($result)) {        
                    echo "<option>" . $tablename[0] . "</option>";
                }
            ?>
        </select>
    </form>

    <h1 class="text-center">Create New Adventure</h1>

    <br>
    <div class="bg-secondary p-3">
        <form method="post" class="container">
            <label for="newTable" class="form-label">Adventure Name:</label>
            <input type="text" name="newTable" class="form-control" required>
            <input type="checkbox" name="viewPublic" data-bs-toggle="collapse" data-bs-target="#editPriviledges">
            <label for="public">View Public?</label>
            <br>
            <div class="collapse" id="editPriviledges">
                <input type="checkbox" name="editPublic">
                <label for="public">Edit Public?</label>
            </div>
            <label for="description" class="form-label">Description:</label>
            <input type="text" name="description" class="form-control mb-2">
            <label for="tableType">Adventure Type: </label>
            <select class="btn btn-primary" name="tableType">
                <option>Classic</option>
                <option>Three</option>
                <option>Loop</option>
                <option>RPG</option>
            </select>
            <input type="submit" name="newSubmit" class="btn btn-primary mb-2">
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
        
        echo "let username = '$user';"; 
    ?>

    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }

    function displayError(message, type) {
        if (!message || message == "" || message == "undefined") {
            return;
        }

        let buttonHtml = "";
        if (type != "primary") {
            buttonHtml = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
        }

        $("#errorHolder").append(`<div class="alert alert-${type}">${message} ${buttonHtml}</div>`);
    }

    displayError(<?php echo "`$error`, '$errorType'";?>);

</script>

</body>
</html>