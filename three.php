<?php
session_start();

// Create connection
$conn = new mysqli("localhost", "root", "", "cyoa3");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//change scenario
if (array_key_exists('select', $_POST)) {
    $_SESSION['scenario'] = $_POST['select'];
}

//change table in database
if (array_key_exists('scenario', $_SESSION)) {
    $table = $_SESSION['scenario'];
}

//select scenario
    echo "<h3> Select Adventure</h3>";
    echo "<form method='post' action='three.php?area=1&old=0'>
        <select name='select' id='scenario' class='btn btn-outline-secondary btn-lg display-2'>";

        $result = mysqli_query($conn, "show tables");
        while($tablename = mysqli_fetch_array($result)) {        
            echo "<option>" . $tablename[0] . "</option>";
        }
    echo "</select>
        <input type='submit' class='btn btn-secondary btn-lg display-2' id='restart' value='Select'>
    </form>";

//enter data into database
if (isset($_POST['submit']) && $_POST['description'] != ""){

    echo mysqli_insert_id($conn);

    echo "<br>";

    //take data from form
    $string = "'" . mysqli_real_escape_string($conn, $_POST["description"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option1"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option2"]) . "' ,";
    $string .= "'" . mysqli_real_escape_string($conn, $_POST["option3"]) . "'";
    echo $string;
    $queryInsert = "INSERT INTO " . $table . " (area, choice1, choice2, choice3) VALUES (" . $string . ")";

    mysqli_query($conn, $queryInsert);
    $new = mysqli_insert_id($conn);

    echo $new;
    
    $old = $_GET['old'];

    //go into link 1 or 2 or 3 from last choice
    if($_GET['choice'] == 1){
        mysqli_query($conn, "UPDATE " . $table . " SET link1='$new' WHERE id='$old'");
    }

    if($_GET['choice'] == 2){
        mysqli_query($conn, "UPDATE " . $table . " SET link2='$new' WHERE id='$old'");
    }

    if($_GET['choice'] == 3){
        mysqli_query($conn, "UPDATE " . $table . " SET link3='$new' WHERE id='$old'");
    }

}

if (isset($_POST['submitLink'])){

    $selected = mysqli_real_escape_string($conn, $_POST['path']);

    echo $selected;

    $queryId = "SELECT id FROM " . $table . " WHERE area='$selected'";

    $result = mysqli_query($conn, $queryId);

    $new = mysqli_fetch_array($result)['id'];

    // $new = $_POST['path'];

    echo $new;

    $old = $_GET['old'];

    if($_GET['choice'] == 1){
        mysqli_query($conn, "UPDATE " . $table . " SET link1='$new' WHERE id='$old'");
    }

    if($_GET['choice'] == 2){
        mysqli_query($conn, "UPDATE " . $table . " SET link2='$new' WHERE id='$old'");
    }

    if($_GET['choice'] == 3){
        mysqli_query($conn, "UPDATE " . $table . " SET link3='$new' WHERE id='$old'");
    }
}

//select area row if there is one
if(array_key_exists('area', $_GET)) {

    $id = $_GET['area'];

    $old = $_GET['old'];
        
    $query = "SELECT * FROM " . $table . " where id = $id";

    $result = mysqli_query($conn, $query);
    
    $row = mysqli_fetch_array($result);

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose 3</title>

    <!-- <script type="text/javascript" src="jquery-3.7.1.min.js"></script> -->

    <!-- <script src="jquery-ui/jquery-ui.js"></script> -->

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
    
    <!-- <a href="three.php?area=1&old=0" class="btn btn-secondary btn-lg display-2">Restart</a> -->

    <h1 class="text-center"><u>Make your own Adventure</u></h1>

    <div class="container p-3 mt-4 border">
        <div class="row">
        <?php
            if(array_key_exists('area', $_GET)) {
                if($row) {
                    echo "<br><h3>" . $row[1] . "</h3>";

                    //check win
                    if(str_contains($row[1], "You win!")) {
                        echo '<a href="three.php?area=1&old=0" id="a1" class="btn btn-info col">Congratulations!</a>';
                    }
                    else {
                        
                    //check die
                    if(str_contains($row[1], "You die.")) {
                        echo '<a href="three.php?area=1&old=0" id="a1" class="btn btn-danger col">Restart</a>';
                    }
                        
                    else {
                        echo "<br><a class='btn btn-primary col' id='a1' href='three.php?area=" . $row[3] . "&old=" . $_GET["area"] . "&choice=1'>" . $row[2] . "</a>";
                        
                        //check second choice
                        if($row[4] != "") {
                            echo "<br><a class='btn btn-warning col' id='a2' href='three.php?area=" . $row[5] . "&old=" . $_GET["area"] . "&choice=2'>" . $row[4] . "</a>";
                        }

                        if($row[6] != "") {
                            echo "<br><a class='btn btn-secondary col' id='a3' href='three.php?area=" . $row[7] . "&old=" . $_GET["area"] . "&choice=3'>" . $row[6] . "</a>";
                        }
                    }

                }}
                else {
                    if(!array_key_exists('choice', $_GET)) {
                        $_GET['choice'] = 0;
                    }

                    echo "New Path found!";
                    echo "<form action='three.php?area=1&old=" . $_GET['old'] . "&choice=" . $_GET['choice'] . "' method='post'>
                        <label for='description'>What happens?</label>
                        <textarea id='area' name='description' class='form-control'></textarea>

                        <label for='option1' class=>Path 1</label>
                        <input type='text' id='option1' name='option1' class='form-control'>

                        <label for='option2'>Path 2</label>
                        <input type='text' id='option2' name='option2' class='form-control'>

                        <label for='option3'>Path 3</label>
                        <input type='text' id='option3' name='option3' class='form-control'>

                        <input type='submit' name='submit' class='btn btn-primary form-control mt-2'>
                    </form>";

                    echo "";

                    //link to made path
                    echo "<form action='three.php?area=1&old=" . $_GET['old'] . "&choice=" . $_GET['choice'] . "' method='post'>";
                    echo '<select class="form-select mt-3" name="path" id="path">';

                    $queryPath = "SELECT area FROM " . $table;
            
                    if ($resultPath = mysqli_query($conn, $queryPath)) {
                        while($path = mysqli_fetch_array($resultPath)) {
                            echo "<option>" . $path["area"] . "</option>";
                        }
                    }
                    echo "</select>
                        <input type='submit' name='submitLink' class='btn btn-dark form-control mt-2'>
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

        <!-- <form action='three.php?area=1&old=0' method='post'>

            <select class="form-select mt-3" name="path" id="path"> -->


    </div>

    <!-- <footer class="bg-dark text-light text-center foot">
        <p class="p-4 mt-3">Website Created By: Willie Downs</p>
        <p class="p-2">&copy; 2024  The Downs Family</p>
        
    </footer> -->


    <script type="text/javascript">

        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }

        document.getElementById('scenario').value = "<?php echo $table;?>";
    
        // var input = document.body;
        document.body.addEventListener("keypress", function(event) {
            if (event.key === "1") {
                
                document.getElementById("a1").click();
            }
            if (event.key === "2") {
                
                document.getElementById("a2").click();
            }
            if (event.key === "3") {
                
                document.getElementById("a3").click();
            }
            if (event.key == "`") {
                
                document.getElementById("restart").click();
            }
        });

    </script>
</body>
</html>