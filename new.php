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
        <select name="scenario" id="tableSelector" class="btn btn-dark">
            <?php
                $result = mysqli_query($conn, "show tables");
                while($tablename = mysqli_fetch_array($result)) {        
                    echo "<option>" . $tablename[0] . "</option>";
                }
            ?>
        </select>
        <input type="submit" id="restart" value="Select" name="selectTable" class="btn btn-outline-dark restart">
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

        </div>
        <div id="inputData" class="hide">

        </div>
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

    $("#tableSelector").val(table);
    loadArea(1,table);

    function loadArea(str,table) {
        if (str == "") {
            document.getElementById("history").innerHTML = "";
            return;
        } else {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {

                    object = JSON.parse(this.responseText);
                    let html = `
                            <input type='number' id='${object.id}' class='areaId debug col-sm-1' readonly value='${object.id}'>
                            <br><h3>${object.area}</h3>`
                        if (object.link1 == 'win') {
                            html += `<button class='btn btn-info col' data-choice='1' data-link='1'>${object.choice1}<span class="debug">(${object.link1})</span></button>`
                        } else
                        if (object.choice1) {
                            html += `<button class='btn btn-primary col' data-choice='1' data-link='${object.link1}'>${object.choice1}<span class="debug">(${object.link1})</span></button>`;
                        } else {
                            html += `<button class='btn btn-danger col restart' data-choice='1' data-link='1'>Restart</button>`
                        }

                        if (object.choice2) {
                            html += `<button class='btn btn-warning col' data-choice='2' data-link='${object.link2}'>${object.choice2}<span class="debug">(${object.link2})</span></button>`
                        }
                        if (debug) {
                            html += `<div class='text-end author debug'>Submitted by-- ${object.author}</div>`
                        };

                    let history = `
                        <tr id='${id}'>;
                            <td class='description'>${object.area}</td>
                            <td class='link' data-choice='1'>${object.choice1}</td>
                            <td class='link' data-choice='2'>${object.choice2}</td>
                        </tr>`

                    // document.getElementById("data").innerHTML = this.responseText;
                    $("#history").append(history);
                    $("#data").html(html);
                }
            };
            console.log("sending data");
            xmlhttp.open("GET",`ajax.php?id=${str}&table=${table}`,true);
            xmlhttp.send();
        }
    }

    $(".restart").on("click", function() {
        let value = 1;
        id = value;
        let table = $("#tableSelector").val();
        console.log("restart");
        $("#inputData").hide();
        $("#data").show();
        loadArea(value, table);
    })

    $(document).on("click", ".currentChoice button", function() {
        let value = $(this).data("link");
        let table = $("#tableSelector").val();
        let choice = $(this).data("choice");

        $("#" + id).css("background-color", "lightblue");
        $(`#${id} td[data-choice=${choice}]`).addClass("active");

        if (value != 0) { 
            loadArea(value, table);
            id = value;
            console.log(id);
        } else {
            // let choice = $(this).data("choice");
            let old = $(".areaId").attr("id");
            console.log(choice, old, table, this);
            newPath(choice, old, table)
        }
    })

    function newPath(choice, oldId, table) {
        let html = `<form method='post' action='addPath.php'>
                    <label for='description'>What happens?</label>
                    <textarea id='area' name='description' class='form-control'></textarea>
                    <label for='option1' class=>Path 1</label>
                    <input type='text' id='option1' name='option1' class='form-control'>
                    <label for='option2'>Path 2</label>
                    <input type='text' id='option2' name='option2' class='form-control'>
                    <input type='text' id='author' name='author' class='form-control w-50 float-end mt-1' value='${username}'>
                    <label for='author' class='float-end mt-2'>Author: </label>
                    <input type='hidden' name='old' value='${oldId}'>
                    <input type='hidden' name='choice' value='${choice}'>
                    <input type='hidden' name='table' value='${table}'>
                    <input type='submit' name='submit' id='submit' class='btn btn-primary form-control mt-2'>
                </form>`;

        $("#inputData").html(html);
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