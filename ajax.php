<?php
session_start();

$type = $_GET['type'];

$conn = new mysqli("localhost", "root", "", "cyoa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
        if ($tableType == "Three") {
            $myObj->choice3 = $row['choice3'];
            $myObj->link3 = $row['link3'];
        }


        $myObj->author = $row['author'];

        $myObj = json_encode($myObj);

        echo $myObj;

    }

    mysqli_close($conn);
} elseif ($type == "add") {
    $table = $_POST['table'];
    $old = $_POST['old'];
    $choice = $_POST['choice'];

    if (isset($_POST['submitLink']) && $_POST['path'] != "") {
        $new = $_POST['path'];
        if ($choice) {
            mysqli_query($conn, "UPDATE $table SET link$choice='$new' WHERE id='$old' LIMIT 1");
        }
    } elseif (isset($_POST['submit']) && $_POST['description'] != ""){

        echo mysqli_insert_id($conn);

        //take data from form
        $string = "'" . mysqli_real_escape_string($conn, $_POST["description"]) . "' ,";
        $string .= "'" . mysqli_real_escape_string($conn, $_POST["option1"]) . "' ,";
        $string .= "'" . mysqli_real_escape_string($conn, $_POST["option2"]) . "' ,";
        if (isset($_POST['option3'])) {
            $string .= "'" . mysqli_real_escape_string($conn, $_POST["option3"]) . "' ,";
        }
        $string .= "'" . mysqli_real_escape_string($conn, $_POST["author"]) . "'";
        //echo $string;
        $queryInsert = "INSERT INTO $table (area, choice1, choice2, author) VALUES ($string)";

        if (isset($_POST['option3'])) {
            $queryInsert = "INSERT INTO $table (area, choice1, choice2, choice3, author) VALUES ($string)";
        }

        mysqli_query($conn, $queryInsert);
        $new = mysqli_insert_id($conn);

        echo $new;

        //enter into whichever choice you did
        if ($choice) {
            mysqli_query($conn, "UPDATE $table SET link$choice='$new' WHERE id='$old' LIMIT 1");
        }

    }

    header("Location: new.php");
    die;

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
}

?>