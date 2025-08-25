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

if(array_key_exists("id", $_COOKIE)) {
    // echo "Cookie: " . $_COOKIE["id"];
    $_SESSION['user'] = $_COOKIE["id"];
}

$error = "";
$errorType = "danger";

$username = "anonymous";
if (array_key_exists('user', $_SESSION)) {
    $username = $_SESSION['user'];
    echo "Welcome: $username";
    echo '<form method="post" id="logout" class="float-end">
                <a href="browse.php" class="btn btn-warning">Browse Tables</a>
                <input type="submit" name="logOut" value="logout" class="btn btn-outline-primary">
            </form>';
} else {
    $error = "Not Logged In! ";
    echo '<div id="login" class="float-end">
            <a type="button" href="index.php" class="btn btn-outline-primary">Login</a>
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

$view = false;
$edit = false;
$admin = false;
$tableType = "Classic";
if ($username != "anonymous"){
    $result = mysqli_query($conn, "SELECT * FROM `tables` WHERE `name` = '" . $_SESSION['scenario'] . "'");
    if ($row = mysqli_fetch_array($result)) {

        if (array_key_exists("type", $row)) {
            $tableType = $row['type'];
            // echo $tableType;
        }
        
        if ($row['creator'] == $username || str_contains($row['editor'], "-($username)") || str_contains($row['editor'], "-all")) {
            $edit = true;
        }
        if ($row['creator'] == $username || str_contains($row['viewer'], "-($username)") || str_contains($row['viewer'], "-all")) {
            $view = true;
        }
        if ($row['creator'] == $username || $username == "WieRD") {
            $admin = true;
        }
    }
} else {
    $_SESSION['scenario'] == "portal";
    $view = true;
}

if (!$view) {
    $error .= "You don't have access to this";
} elseif (!$edit) {
    $error .= "View Only";
    $errorType = "primary";
}

                
echo '<select class="form-select" name="path" id="path" value="">';
    echo '<option value="0">0=New Area</option>';
$queryPath = "SELECT `area`, `id` FROM " . $_SESSION['scenario'];

if ($resultPath = mysqli_query($conn, $queryPath)) {
    while($path = mysqli_fetch_array($resultPath)) {
        $area = substr($path['area'], 0, 80) . "...";
        $thisId = $path['id'];
        echo "<option value='$thisId'>$thisId=" . htmlspecialchars($area) . "</option>";
    }
}
echo "</select>
    <button type='button' class='btn btn-dark form-control mt-2' id='searchArea'>Search</button>";

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

    <div id="errorHolder"></div>

    <?php if ($admin) {echo "<button class='btn btn-success float-end' id='editPath'>Edit this path</button>";} ?>

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
        <div id="data" class="">
            <input type='number' id='currentArea' class='areaId debug form-control-plaintext col-sm-1' readonly value=''>
            <br>
            <h3 id="areaDescription"></h3>
            <div id="choiceButtons" class="row m-0">

            </div>
            <div class='text-end author debug'>Submitted by-- <span id="author"></span></div>


        </div>
        <div id="inputData" class="hide">
            <!-- Create new area and post link to old path -->
            <div class="prev-choice">
                <p id="old-description"></p>
                <p id="old-choice" class="btn btn-secondary disabled"></p>
            </div>
            <h4 class="new-path">New Path Found!</h4>
            <form method='post' action='ajax.php?type=add' id='addPathForm'>
                <label for='description'>What happens?</label>
                <textarea id='area' name='description' class='form-control'></textarea>
                
                <button class='colorToggle btn btn-info' type='button'>Add Color</button>
                <div class='colorInput'></div>
                <br>
                <div class="row m-0">
                    <label for='option1' class=>Path 1</label>
                    <input type='text' id='option1' name='option1' class='form-control'>
                    <button type='button' class='btn btn-outline-dark loopLinkPath col-4' id='loop1' data-loop='1'>Loop Link</button>
                    <select class='form-select col pathSelect' name='pathLink1' id='pathLink1'><option value='0' class='default'>Link: <span>0</span></option></select>

                    <label for='option2'>Path 2</label>
                    <input type='text' id='option2' name='option2' class='form-control'>
                    <button type='button' class='btn btn-outline-dark loopLinkPath col-4' id='loop2' data-loop='2'>Loop Link</button>
                    <select class='form-select col pathSelect' name='pathLink2' id='pathLink2'><option value='0' class='default'>Link: <span>0</span></option></select>
                    
                    <?php if ($tableType == "Three" || $tableType == "RPG") {echo "
                        <label for='option3'>Path 3</label>
                        <input type='text' id='option3' name='option3' class='form-control'>
                        <button type='button' class='btn btn-outline-dark loopLinkPath col-4' id='loop3' data-loop='3'>Loop Link</button>
                        <select class='form-select col pathSelect' name='pathLink3' id='pathLink3'><option value='0' class='default'>Link: <span>0</span></option></select>
                    ";} ?>
                </div>
                
                <div class='d-flex w-100 flex-row-reverse'>
                    <input type='text' id='newPathAuthor' name='author' class='form-control w-50 align-end mt-1' value=''>
                    <label for='author' class='align-end mt-2'>Author: </label>
                </div>
                
                <input type='hidden' name='old' value='' id='oldId'>
                <input type='hidden' name='choice' value='' id='oldChoice'>
                <input type='hidden' name='table' value='' id='oldTable'>
                <input type='submit' name='submit' id='submit' class='btn btn-primary form-control mt-2 mb-2'>

                <!-- Add link to prior area -->
                <?php if($tableType == "Three" || $tableType == "Loop" || $tableType == "RPG") {
                    echo '<select class="form-select" name="path" id="path" value="">';
                        echo '<option value="0">0=New Area</option>';
                    $queryPath = "SELECT `area`, `id` FROM " . $_SESSION['scenario'];
            
                    if ($resultPath = mysqli_query($conn, $queryPath)) {
                        while($path = mysqli_fetch_array($resultPath)) {
                            $area = substr($path['area'], 0, 80) . "...";
                            $thisId = $path['id'];
                            echo "<option value='$thisId'>$thisId=$area</option>";
                        }
                    }
                    echo "</select>
                        <input type='submit' name='submitLink' class='btn btn-dark form-control mt-2 loopLink' id='loopLinkArea' value='Loop Link' disabled>
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
    let view = false;
    let rpg = false;
    let update = false;
    let areaColor = null;

    <?php if (array_key_exists("scenario", $_SESSION)) {
        echo "table = '" . $_SESSION['scenario'] . "';";
        }
        
        echo "let username = '$username';";
        if ($edit) {
            echo "edit = $edit;";
        }
        if ($view) {
            echo "view = $view;";
        }
        if ($tableType == "RPG") {
            echo "rpg = true;";
        }
    ?>

    if (!edit) {
        
        if (username == "anonymous" && table == "portal") {
            $("#addPathForm").html(`You don't have access to add to this!<br>Login to be able to edit.<br>
                <button class='restart btn btn-primary'>Restart</button><a type='button' href='index.php' class='btn btn-outline-primary'>Login</a>`);
            $("#tableSelector").addClass("disabled");
            displayError("Login to view other tables", "warning");
        } else {
            $("#addPathForm").html("You don't have access to add to this!<br><button class='restart btn btn-primary'>Restart</button>");
        }
    }
    if (!view) {
        $(".main-box").html("You don't have access to view this!<br><a href='browse.php' class='btn btn-warning'>Browse</a>");
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

                        let buttonHtml = `<button class='btn btn-primary col-md' data-choice='1' data-link='${object.link1}'><span class="choice-text">${object.choice1}</span><span class="debug">(${object.link1})</span></button>`;
                        if (object.link1 == 'win') {
                            buttonHtml = `<button class='btn btn-info col-md' data-choice='1' data-link='1'><span class="choice-text">${object.choice1}</span><span class="debug">(${object.link1})</span></button>`
                        }
                        if (!object.choice1) {
                            buttonHtml = `<button class='btn btn-danger col-md restart' data-choice='1' data-link='1'><span class="choice-text"></span>Restart</button>`
                        }
                        if (object.choice2) {
                            buttonHtml += `<button class='btn btn-warning col-md' data-choice='2' data-link='${object.link2}'><span class="choice-text">${object.choice2}</span><span class="debug">(${object.link2})</span></button>`
                        }
                        if (object.choice3) {
                            buttonHtml += `<button class='btn btn-success col-md' data-choice='3' data-link='${object.link3}'><span class="choice-text">${object.choice3}</span><span class="debug">(${object.link3})</span></button>`
                        }
                        if (object.color) {
                            $("body").css("background-color", object.color);
                            areaColor = object.color;
                        } else {areaColor = null}

                        $("#currentArea").val(object.id);
                        $("#areaDescription").html(object.area);
                        $("#author").html(object.author)

                        $("#choiceButtons").html(buttonHtml);

                        if (rpg) {
                            checkScore(object.area);
                            $("#choiceButtons button").each(function() {
                                if (!checkAbility($(this).text())) {
                                    $(this).addClass("disabled");
                                }
                            })
                        }

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
        // let table = $("#tableSelector").val();
        let choice = $(this).data("choice");
        let text = $(this).html();

        if (rpg) {
            checkScore($(this).text());
        }

        $("#" + id).css("background-color", "lightblue");
        $(`#${id} td[data-choice=${choice}]`).addClass("active");

        $("#inputData").css("display", "none");

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

    $(".colorToggle").on("click", function() {
        if ($(".colorInput").html() == "") {
            $(".colorInput").html("<label for='areaColor'>Color: </label><input type='color' name='areaColor' value='#ffffff'>")
        } else {
            $(".colorInput").html("")
        }
    })

    $("#editPath").on("click", function() {
        $("#area").val($("#areaDescription").html());
        $("#option1").val($("button[data-choice='1'] .choice-text").text());
        $("#option2").val($("button[data-choice='2'] .choice-text").text());
        $("#option3").val($("button[data-choice='3'] .choice-text").text());
        $(".pathSelect").css("display", "block");
        // $("#pathLink1").html('<option>'+$("button[data-choice='1']").data("link")+'</option>');
        // $("#pathLink2").html('<option>'+$("button[data-choice='2']").data("link")+'</option>');
        $("#pathLink1 .default span").html($("button[data-choice='1']").data("link"));
        $("#pathLink1 .default").val($("button[data-choice='1']").data("link"));
        $("#pathLink2 .default span").html($("button[data-choice='2']").data("link"));
        $("#pathLink2 .default").val($("button[data-choice='2']").data("link"));
        $("#pathLink3 .default span").html($("button[data-choice='3']").data("link"));
        $("#pathLink3 .default").val($("button[data-choice='3']").data("link"));
        $("#newPathAuthor").val($("#author").html());
        $("#oldId").val($("#currentArea").val());
        $("#oldTable").val(table);
        $("#inputData").show();
        $("#submit").attr("name", "submitUpdate");
        $("#submit").val("Update");
        $(".new-path").html("Update path: " + $("#oldId").val());
        update = true;

        if (areaColor) {
            $(".colorInput").html(`<label for='areaColor'>Color: </label><input type='color' name='areaColor' value='${areaColor}'>`)
        }
    })

    $("#history").on("click", "tr", function() {
        let value = $(this).attr("id");
        // let table = $("#tableSelector").val();
        console.log(value);

        loadArea(value, table);
    })

    function newPath(choice, oldId, table) {
        console.log(oldId);
        if (oldId == 0 || oldId == "undefined") {
            $(".prev-choice").hide();
            $(".new-path").html("Beginning of a new adventure: " + table);
        }
        $("#addPathForm input:not([type='submit']), #addPathForm textarea").val("");
        $(".default").html("No Path Link");
        $(".default").val(0);

        $("#oldId").val(oldId);
        $("#oldChoice").val(choice);
        $("#oldTable").val(table);
        $("#newPathAuthor").val(username);
        $(".new-path").html("New Path Found!");

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

    function displayError(message, type) {
        if (!message || message == "" || message == "undefined") {
            return;
        }

        let buttonHtml = "";
        if (type != "primary" || true) {
            buttonHtml = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
        }

        $("#errorHolder").append(`<div class="alert alert-${type} error">${message}${buttonHtml}</div>`);
    }

    displayError(<?php echo "`$error`, '$errorType'";?>);

    //only activate loop link button on change
    $(document).on("change", "#path", function() {
        $("#loopLinkArea").attr("disabled", false);
    })

    //loop link path
    $(document).on("click", ".loopLinkPath", function() {
        let html = $(this).html();
        let clicked = $(this);
        let buttonNumber = $(this).data("loop");
        console.log(buttonNumber);
        
        if (html == "Loop Link") {
            if ($("#pathLink" + buttonNumber + " option").length > 2) {
                $("#pathLink" + buttonNumber).show();
                clicked.html("Remove Link");
            
            } else {
                $("#pathLink" + buttonNumber).show();
                $("#pathLink" + buttonNumber).append($("#path").html());
                clicked.html("Remove Link");
            }
        
        } else if (!update){
            $("#pathLink" + buttonNumber).hide();
            $("#pathLink" + buttonNumber).val(0);
            $(this).html("Loop Link");
        }
        if (update) {
            $(this).html("Change Link");
            $(this).addClass("disabled");
        }
    })

    <?php if ($tableType == "Three" || $tableType == "RPG" || $tableType == "Loop") {echo "
    $('.loopLinkPath').css('display', 'block');
    ";}?>

</script>

<?php if ($tableType == "RPG") {
    echo "<script type='text/javascript' src='rpgmode.js'></script>";
} ?>

</body>
</html>