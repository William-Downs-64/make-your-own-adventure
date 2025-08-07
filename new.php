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
                <a href="browse.php" class="btn btn-secondary">Browse Tables</a>
                <input type="submit" name="logOut" value="logout" class="btn btn-outline-primary">
            </form>';
} else {
    echo "Not Logged in!";
    echo '<div id="login" class="float-end">
            <a type="button" href="/login" class="btn btn-outline-primary">Login</a>
        </div>';
}

if (array_key_exists('scenario', $_POST)) {
    if ($_POST['scenario'] == "New Table") {
        header('location: createNew.php');
    }
    $_SESSION['scenario'] = $_POST['scenario'];
}

if (!array_key_exists('scenario', $_SESSION)) {
    $_SESSION['scenario'] = "portal";
}

$edit = false;
$tableType = "Classic";
if ($username != "anonymous"){
    $result = mysqli_query($conn, "SELECT * FROM `tables` WHERE `name` = '" . $_SESSION['scenario'] . "'");
    if ($row = mysqli_fetch_array($result)) {

        if (array_key_exists("type", $row)) {
            $tableType = $row['type'];
            echo $tableType;
        }
        
        if ($row['creator'] == $username || str_contains($row['editor'], "-($username)") || str_contains($row['editor'], "-all")) {
            $edit = true;
        }
    }
}
if (!$edit) {
    echo "<div class='error'>View Only</div>";
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

    <title>Choose Your Own Adventure</title>

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
        #inputData {
            display: none;
        }
        .prev-choice {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
            font-size: 20px;
            background-color: lightblue;
        }
        .error {
            background-color: red;
            position: absolute;
            top: 30px;
            left: 45%;
            padding: 10px;

        }
  </style>

</head>
<body>

    <form method="post">
        <select name="scenario" id="tableSelector" class="btn btn-dark">
            <option>portal</option>
            <?php
                $string = "SELECT `myTables` FROM `users` WHERE `username` = '$username'";
                $result = mysqli_query($conn, $string);
                $row = mysqli_fetch_array($result);
                $tableArray = explode("-", preg_replace("/[()]/", "", $row['myTables']));
                $tableArray = array_slice($tableArray, 1);
                
                foreach($tableArray as $value) {        
                    echo "<option value='$value'>" . str_replace("_", " ", $value) . "</option>";
                }
            ?>
            <option class="bg-info"><a href="createNew.php">New Table</a></option>
        </select>
        <input type="submit" id="restart" value="Select" class="btn btn-outline-dark restart">
        <input type="checkbox" id="debug-toggle">
    </form>



    <br>
    <div class="history-container">
        <table class="table debug table-bordered">
            <thead>
            <tr>
                <th>Area</th>
                <th>choice1</th>
                <th>choice2</th>
            </tr>
            </thead>
            <tbody id="history">

            </tbody>
        </table>
    </div>

    <div class="currentChoice container p-3 mt-4 border main-box">
        <div id="data" class="row">
            <input type='number' id='currentArea' class='areaId debug form-control-plaintext col-sm-1' readonly value=''>
            <br>
            <h3 id="areaDescription"></h3>
            <div id="choiceButtons" class="row">

            </div>
            <div class='text-end author debug'>Submitted by-- <span id="author"></span></div>


        </div>
        <div id="inputData" class="hide">
            <!-- Create new area and post link to old path -->
            <form method='post' action='ajax.php?type=add'>
                <div class="prev-choice">
                    <p id="old-description"></p>
                    <p id="old-choice" class="btn btn-secondary disabled"></p>
                </div>
                <h4 class="new-path">New Path Found!</h4>
                <label for='description'>What happens?</label>
                <textarea id='area' name='description' class='form-control'></textarea>
                <label for='option1' class=>Path 1</label>
                <input type='text' id='option1' name='option1' class='form-control'>
                <label for='option2'>Path 2</label>
                <input type='text' id='option2' name='option2' class='form-control'>
                <?php if ($tableType == "Three") {echo "
                    <label for='option3'>Path 3</label>
                    <input type='text' id='option3' name='option3' class='form-control'>
                ";} ?>
                <input type='text' id='newPathAuthor' name='author' class='form-control w-50 float-end mt-1' value=''>
                <label for='author' class='float-end mt-2'>Author: </label>
                <input type='hidden' name='old' value='' id='oldId'>
                <input type='hidden' name='choice' value='' id='oldChoice'>
                <input type='hidden' name='table' value='' id='oldTable'>
                <input type='submit' name='submit' id='submit' class='btn btn-primary form-control mt-2'>

                <!-- Add link to prior area -->
                <?php if($tableType == "Three" || $tableType == "Loop") {
                    echo '<select class="form-select mt-3" name="path" id="path">';

                    $queryPath = "SELECT `area`, `id` FROM " . $_SESSION['scenario'];
            
                    if ($resultPath = mysqli_query($conn, $queryPath)) {
                        while($path = mysqli_fetch_array($resultPath)) {
                            $area = substr($path['area'], 0, 80) . "...";
                            echo "<option value='" . $path["id"] . "'>$area</option>";
                        }
                    }
                    echo "</select>
                        <input type='submit' name='submitLink' class='btn btn-dark form-control mt-2' value='Loop Link'>
                        </form>";
                } ?>

            </form>
                    
        </div>
    </div>

<script>

    var id = 0;
    let debug = false;
    let table = "portal";
    let edit = false;

    <?php if (array_key_exists("scenario", $_SESSION)) {
        echo "table = '" . $_SESSION['scenario'] . "';";
        }
        
        echo "let username = '$username';";
        if ($edit) {
            echo "edit = $edit;";
        } 
    ?>

    if (!edit) {
        $("#inputData").html("You don't have access to edit this<br><button class='restart btn btn-primary'>Restart</button> ");
    }

    $("#tableSelector").val(table);
    loadArea(1,table);

    function loadArea(area,table) {
        if (area == "") {
            document.getElementById("history").innerHTML = "";
            return;
        } else {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {

                    if (!this.responseText) {
                        console.log("nothing");
                        newPath(0,0,table);
                        return;
                    } else {
                        let object = JSON.parse(this.responseText);

                        let buttonHtml = `<button class='btn btn-primary col' data-choice='1' data-link='${object.link1}'>${object.choice1}<span class="debug">(${object.link1})</span></button>`;
                        if (object.link1 == 'win') {
                            buttonHtml = `<button class='btn btn-info col' data-choice='1' data-link='1'>${object.choice1}<span class="debug">(${object.link1})</span></button>`
                        }
                        if (!object.choice1) {
                            buttonHtml = `<button class='btn btn-danger col restart' data-choice='1' data-link='1'>Restart</button>`
                        }
                        if (object.choice2) {
                            buttonHtml += `<button class='btn btn-warning col' data-choice='2' data-link='${object.link2}'>${object.choice2}<span class="debug">(${object.link2})</span></button>`
                        }
                        if (object.choice3) {
                            buttonHtml += `<button class='btn btn-success col' data-choice='3' data-link='${object.link3}'>${object.choice3}<span class="debug">(${object.link3})</span></button>`
                        }

                        $("#currentArea").val(object.id);
                        $("#areaDescription").html(object.area);
                        $("#author").html(object.author)

                        $("#choiceButtons").html(buttonHtml);

                        let history = `
                            <tr id='${id}'>;
                                <td class='description'>${object.area}</td>
                                <td class='link' data-choice='1'>${object.choice1}</td>
                                <td class='link' data-choice='2'>${object.choice2}</td>
                            </tr>`

                        // document.getElementById("data").innerHTML = this.responseText;
                        $("#history").append(history);
                        // $("#data").html(html);
                    }
                }
            };
            console.log("fetching data for area " + area);
            xmlhttp.open("GET",`ajax.php?id=${area}&table=${table}&type=load&tableType=<?php echo $tableType ?>`,true);
            xmlhttp.send();
        }
    }

    $(".restart").on("click", function() {
        id = 1;
        let table = $("#tableSelector").val();
        console.log("restart");
        $("#inputData").hide();
        $("#data").show();
        loadArea(id, table);
    })

    $(document).on("click", ".currentChoice #choiceButtons button", function() {
        let value = $(this).data("link");
        let table = $("#tableSelector").val();
        let choice = $(this).data("choice");
        let text = $(this).html();

        $("#" + id).css("background-color", "lightblue");
        $(`#${id} td[data-choice=${choice}]`).addClass("active");

        if (value != 0) { 
            loadArea(value, table);
            id = value;
            console.log("id: " + id);
        } else {
            // let choice = $(this).data("choice");
            let old = $(".areaId").val();
            $("#old-description").html($("#areaDescription").html())
            $("#old-choice").html(text);
            console.log(choice, old, table, this);
            newPath(choice, old, table);
        }
    })

    function newPath(choice, oldId, table) {
        console.log(oldId);
        if (oldId == 0 || oldId == "undefined") {
            $(".prev-choice").hide();
            $(".new-path").html("Beginning of a new adventure: " + table);
        }
        $("#oldId").val(oldId);
        $("#oldChoice").val(choice);
        $("#oldTable").val(table);
        $("#newPathAuthor").val(username);

        $("#inputData").show();
        $("#data").hide();

    }

    $("#debug-toggle").on("click", function() {
        if ($(this).prop("checked")) {
            debug = true;
            $(":root").css("--debug", "inherit");
        } else {
            debug = false;
            $(":root").css("--debug", "none");
        }
    })

</script>

</body>
</html>