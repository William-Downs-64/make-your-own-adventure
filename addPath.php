<?php

$conn = new mysqli("localhost", "root", "", "cyoa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

print_r($_POST);

$table = $_POST['table'];
$old = $_POST['old'];
$choice = $_POST['choice'];

if (isset($_POST['submit']) && $_POST['description'] != ""){

    echo mysqli_insert_id($conn);

    //take data from form
    $string = "'" . mysqli_real_escape_string($conn, $_POST["description"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option1"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option2"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["author"]) . "'";
    //echo $string;
    $queryInsert = "INSERT INTO " . $table . " (area, choice1, choice2, author) VALUES (" . $string . ")";

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

?>