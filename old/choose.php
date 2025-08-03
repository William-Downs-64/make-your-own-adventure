<?php

// Create connection
$conn = new mysqli("localhost", "root", "", "cyoa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(array_key_exists('area', $_GET)) {

    $id = $_GET['area'];

    $old = $_GET['old'];
        
    $query = "SELECT * FROM portal where id = $id";

    $result = mysqli_query($conn, $query);
    
    $row = mysqli_fetch_array($result);

}

if (isset($_POST['submit']) && $_POST['description'] != ""){

    echo mysqli_insert_id($conn);

    echo "<br>";

    $string = "'" . mysqli_real_escape_string($conn, $_POST["description"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option1"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option2"]) . "'";
    echo $string;
    $queryInsert = "INSERT INTO portal (area, choice1, choice2) VALUES (" . $string . ")";

    mysqli_query($conn, $queryInsert);
    $new = mysqli_insert_id($conn);

    echo $new;
    

    if($_GET['choice'] == 1){
        mysqli_query($conn, "UPDATE portal SET link1='$new' WHERE id='$old'");
    }

    if($_GET['choice'] == 2){
        mysqli_query($conn, "UPDATE portal SET link2='$new' WHERE id='$old'");
    }

}
else {
    //echo "An error occurred.";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose your own Adventure</title>

    <script type="text/javascript" src="jquery-3.7.1.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
          crossorigin="anonymous"></script>
        
          
    <style>

        .foot {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
    
    <a href="choose.php?area=1&old=0" class="btn btn-secondary btn-lg display-2">Restart</a>

    <h1 class="text-center"><u>Make your own Adventure</u></h1>

    <div class="container p-3 mt-4 border">
        <div class="row">
        <?php
            if(array_key_exists('area', $_GET)) {
                if($row) {
                    echo "<br><h3>" . $row[1] . "</h3>";

                    if(str_contains($row[1], "You win!")) {
                        echo '<a href="choose.php?area=1&old=0" class="btn btn-info col">Congratulations!</a>';
                    }
                    else {
                        
                        if(!str_contains($row[1], "You die.")) {
                            echo "<br><a class='btn btn-primary col' href='choose.php?area=" . $row[3] . "&old=" . $_GET["area"] . "&choice=1'>" . $row[2] . "</a>";
                            
                            if($row[4] != "") {
                            echo "<br><a class='btn btn-warning col' href='choose.php?area=" . $row[5] . "&old=" . $_GET["area"] . "&choice=2'>" . $row[4] . "</a>";
                            }
                        }
                        else {
                            echo '<a href="choose.php?area=1&old=0" class="btn btn-danger col">Restart</a>';
                        }
                    }

                }
                else {
                    echo "New Path found!";
                    echo "<form action='choose.php?area=1&old=" . $_GET['old'] . "&choice=" . $_GET['choice'] . "' method='post'>
                        <label for='description'>What happens?</label>
                        <textarea id='area' name='description' class='form-control'></textarea>
                        <label for='option1' class=>Path 1</label>
                        <input type='text' id='option1' name='option1' class='form-control'>
                        <label for='option2'>Path 2</label>
                        <input type='text' id='option2' name='option2' class='form-control'>
                        <input type='submit' name='submit' class='btn btn-primary form-control mt-2'>
                    </form>";
                }
            }


        ?>
        </div>
        <!-- <form action='choose.php?area=1&old=0' method='post'>
            <label for='description'>What happens?</label>
            <textarea id='area' name='description' class='form-control'></textarea>
            <label for='option1' class=>Path 1</label>
            <input type='text' id='option1' name='option1' class='form-control'>
            <label for='option2'>Path 2</label>
            <input type='text' id='option2' name='option2' class='form-control'>
            <input type='submit' name='submit'>
        </form> -->

    </div>

    <footer class="bg-dark text-light text-center foot">
        <p class="p-4 mt-3">Website Created By: Willie Downs</p>
        <p class="p-2">&copy; 2024  The Downs Family</p>
        
    </footer>


    <script type="text/javascript">

        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }

    </script>
</body>
</html>