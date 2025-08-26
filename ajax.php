<?php
session_start();

$type = $_GET['type'];
if (array_key_exists("table", $_POST)) {
    $table = $_POST['table'];
}
if (array_key_exists("old", $_POST)) {
    $old = $_POST['old'];
}
if (array_key_exists("choice", $_POST)) {
    $choice = $_POST['choice'];
}

$conn = new mysqli("localhost", "root", "", "cyoa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//load area
if ($type == "load") {
    $id = $_GET['id'];
    $table = $_GET['table'];
    $tableType = $_GET['tableType'];

    // echo "$q";

    $sql = "SELECT * FROM $table WHERE `id` = '$id';";

    $result = mysqli_query($conn , $sql);

    while($row = mysqli_fetch_array($result)) {
        $myObj = new stdClass();
        $myObj->id = "$id";
        $myObj->area = $row['area'];
        $myObj->choice1 = $row['choice1'];
        $myObj->link1 = $row['link1'];
        if(str_contains(strtolower($row['area']), "you win!")) {
            $myObj->choice1 = 'Congratulations';
            $myObj->link1 = 'win';
        }
        $myObj->choice2 = $row['choice2'];
        $myObj->link2 = $row['link2'];
        if (array_key_exists("choice3", $row)) {
            $myObj->choice3 = $row['choice3'];
            $myObj->link3 = $row['link3'];
        }
        if (array_key_exists("color", $row)) {
            $myObj->color = $row['color'];
        }


        $myObj->author = $row['author'];

        $myObj = json_encode($myObj);

        echo $myObj;

    }

    mysqli_close($conn);

//looplink
} elseif ($type == "loop") {
    if (!array_key_exists("path", $_POST)) {
        die("Path not set");
    }
    if (!$table || !$old || !$choice) {
        die("Missing parameters: $table, $old, $choice");
    }

    $new = $_POST['path'];
    if ($choice) {
        mysqli_query($conn, "UPDATE $table SET link$choice='$new' WHERE id='$old' LIMIT 1");
    }
    echo "Loop Link";

} elseif ($type == "update") {
    if (!$table || !$old) {
        die("Missing parameters");
    }

    $queryUpdate = "UPDATE `$table` SET 
        area = '" . mysqli_real_escape_string($conn, $_POST["description"]) . "', 
        choice1 = '" . mysqli_real_escape_string($conn, $_POST["option1"]) . "', 
        choice2 = '" . mysqli_real_escape_string($conn, $_POST["option2"]) . "', 
        link1 = '" . $_POST["pathLink1"] . "', 
        link2 = '" . $_POST["pathLink2"] . "', ";
    if (isset($_POST['option3'])) {
        $queryUpdate .= "choice3 = '" . mysqli_real_escape_string($conn, $_POST["option3"]) . "', 
            link3 = '" . $_POST["pathLink3"] . "', ";
    }
    if (isset($_POST['areaColor'])) {
        $queryUpdate .= "color = '" . mysqli_real_escape_string($conn, $_POST["areaColor"]) . "', ";
    }
    $queryUpdate .= "author = '" . mysqli_real_escape_string($conn, $_POST["author"]) . "'
        WHERE `id` = $old LIMIT 1";
    
    mysqli_query($conn, $queryUpdate);

    $response = new stdClass();
    $response->id = $old;
    $response->description = htmlspecialchars(substr($_POST["description"], 0, 80)) . "...";
    $response->error = mysqli_error($conn);
    $response->message = "Updated area: $old";

    echo json_encode($response);

    // echo "Updating: ";
    // echo $queryUpdate;

//add new area
} elseif ($type == "add") {
    if (!$table || !$old) {
        die("Missing parameters");
    }

    //take data from form
    $string = "'" . mysqli_real_escape_string($conn, $_POST["description"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option1"]) . "' ,";
    $string .= "'" . $_POST['pathLink1'] . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option2"]) . "' ,";
    $string .= "'" . $_POST['pathLink2'] . "' ,";
    if (isset($_POST['option3'])) {
        $string .= "'" . mysqli_real_escape_string($conn, $_POST["option3"]) . "' ,";
        $string .= "'" . $_POST['pathLink3'] . "' ,";
    }
    if (isset($_POST['areaColor'])) {
        $string .= "'" . mysqli_real_escape_string($conn, $_POST["areaColor"]) . "' ,";
    }
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["author"]) . "'";
    
    $queryInsert = "INSERT INTO $table (area, choice1, link1, choice2, link2, ";
    if (isset($_POST['option3'])) {
        $queryInsert .= "choice3, link3, ";
    }
    if (isset($_POST['areaColor'])) {
        $queryInsert .= "color, ";
    }
    $queryInsert .= "author) VALUES ($string)";

    mysqli_query($conn, $queryInsert);
    $new = mysqli_insert_id($conn);

    // echo "Created new area: ";
    // echo $new;
    // $area = substr($path['area'], 0, 80) . "...";
    // $thisId = $path['id'];
    // echo "<option value='$thisId'>$thisId=" .  htmlspecialchars($area) . "</option>";

    $response = new stdClass();
    $response->id = $new;
    $response->description = htmlspecialchars(substr($_POST["description"], 0, 80)) . "...";
    $response->error = mysqli_error($conn);
    $response->message = "Added area: $new";

    echo json_encode($response);

    //enter into whichever choice you did
    if ($choice) {
        mysqli_query($conn, "UPDATE $table SET link$choice='$new' WHERE id='$old' LIMIT 1");
    }


} elseif ($type == "myTables") {
    $table = $_GET['table'];
    $user = $_SESSION['user'];
    // $user = "Dr. Frankenstein";
    $function = $_GET['function'];

    $sql = "SELECT `myTables` FROM `users` WHERE `username` = '$user' && `myTables` LIKE '%-($table)%'";
    // echo $sql;
    $result = mysqli_query($conn , $sql);

    // print_r($result);

    if (mysqli_fetch_array($result)) {
        if ($function == "remove") {
            $remove = "UPDATE `users` SET `myTables` = REPLACE(`myTables`, '-($table)', '') WHERE `username` = '$user'";
            mysqli_query($conn, $remove);
            echo "removed table from your list";
            if ($_SESSION['scenario'] == $table) {
                $_SESSION['scenario'] = "portal";
            }
        } else {
            echo "already in myTables";
        }
        die;
    } elseif ($function == "add") {
        $update = "UPDATE `users` SET `myTables` = CONCAT(`myTables`, '-($table)') WHERE `username` = '$user'";
        mysqli_query($conn, $update);
        $_SESSION['scenario'] = $table;
        echo "added table to your list";
        // echo $update;
    }
} else {
    echo "Error: ";
    echo $type;
}

?>