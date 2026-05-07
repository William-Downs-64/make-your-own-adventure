<?php
session_start();

include('connection.php');

if(array_key_exists('logOut', $_POST)) {
    session_unset();
    
    setcookie("id", "", time() - 60 * 60);

    echo "logging Out";
}

if(array_key_exists("id", $_COOKIE)) {
    $_SESSION['user'] = $_COOKIE["id"];
}

$error = "";
$errorType = "danger";

$username = "anonymous";
if (array_key_exists('user', $_SESSION)) {
    $username = $_SESSION['user'];
    echo "Welcome: $username";
    echo '<form method="post" id="logout" class="float-end">
                <select id="themeSelect" class="btn btn-secondary">
                    <option>Normal</option>
                    <option>1</option>
                    <option>grayscale</option>
                    <option>Dark</option>
                    <option>Gradient</option>
                    <option>Blues</option>
                </select>

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
} else {
    $_SESSION['scenario'] = "portal";
}

$view = false;
$edit = false;
$admin = false;
$tableType = "Classic";
$btnMax = 2;
if ($username != "anonymous"){
    $result = mysqli_query($conn, "SELECT * FROM `tables` WHERE `name` = '" . $_SESSION['scenario'] . "'");
    if ($row = mysqli_fetch_array($result)) {

        if (array_key_exists("type", $row)) {
            $tableType = $row['type'];
        }
        $btnMax = $row['choices'];

        if ($row['creator'] == $username || str_contains($row['editor'], "-($username)") || str_contains($row['editor'], "-all")) {
            $edit = true;
        }
        if ($row['creator'] == $username || str_contains($row['viewer'], "-($username)") || str_contains($row['viewer'], "-all")) {
            $view = true;
        }
        if ($row['creator'] == $username || $username == "WieRD" || $username == "Willie") {
            $admin = true;
        }
    }
} else {
    $_SESSION['scenario'] == "portal";
    $view = true;
}

// if (!$view) {
//     $error .= "You don't have access to this";
// } elseif (!$edit) {
//     $error .= "View Only";
//     $errorType = "primary";
// }
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

    <div class="tint-overlay"></div>

    <!-- Table selection -->
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

    <!-- Alerts -->
    <div id="errorHolder"></div>

    <?php if ($admin) {echo "<button class='btn btn-success float-end' id='editPath'>Edit this path</button>";} ?>

    <br>
    <!-- Debug History -->
    <div class="history-container">
        <table class="table debug table-bordered">
            <thead>
            <tr>
                <th>Area</th>
                <th>choice1</th>
                <th>choice2</th>
                <th>choice3</th>
            </tr>
            </thead>
            <tbody id="history">

            </tbody>
        </table>
    </div>
    <div class="debug">
        <form method="get">
            <input type="number" id="debugArea" name="area" value="<?php if (array_key_exists('area', $_SESSION)) { echo $_SESSION['area']; } ?>">
            <button class="btn btn-dark" id="debugAreaButton">Set Area</button>
        </form>
    </div>

    <!-- Main Area -->
    <div class="currentChoice container p-3 mt-4 main-box">
        <!-- Loaded Area -->
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
            <button class='btn btn-outline-secondary float-end' id='backBtn'>Back</button>

            <form method='post' id='addPathForm'>
                <label for='description'>What happens?</label>
                <textarea id='area' name='description' class='form-control areaInput'></textarea>
                
                <button class='colorToggle btn btn-info' type='button'>Add Color</button>
                <div class='colorInput'></div>
                <br>

                <div class="row m-0">

                    <?php for ($i = 1; $i <= $btnMax; $i++) {echo "
                        <label for='option$i'>Path $i</label>
                        <input type='text' id='option$i' name='option$i' class='form-control areaInput'>
                        <button type='button' class='btn btn-outline-dark loopLinkPath col-4' id='loop$i' data-loop='$i'>Loop Link</button>
                        <input class='form-control col pathSelect' name='pathLink$i' id='pathLink$i' list='path'>
                    ";}?>
                </div>

                <div id='toggleRPG'></div>
                <div id='rpg' class='collapse'>
                    <div id='addChange'></div>
                    <div id='addRule'></div>
                </div>
                
                <div class='d-flex w-100 justify-content-end'>
                    <label for='author' class='m-2'>Author: </label>
                    <input type='text' id='newPathAuthor' name='author' class='form-control w-50 mt-1' value=''>
                </div>

                <input type='checkbox' name='stay' id='stay' class='form-check-input'>
                <label for='stay' class='form-check-label'>Stay?</label>

                <input type='hidden' name='old' value='' id='oldId'>
                <input type='hidden' name='choice' value='' id='oldChoice'>
                <input type='hidden' name='table' value='' id='oldTable'>
                <button type='button' name='add' id='submit' class='btn btn-primary form-control mt-2 mb-2 submit'>Submit</button>

                <!-- Add link to prior area -->
                <?php if($tableType != "Classic") {
                    echo '<input class="form-control" list="path" id="pathInput">';
                    echo '<datalist class="" name="path" id="path" value="">';
                        echo '<option value="0">0=New Area</option>';
                    $queryPath = "SELECT `area`, `id` FROM " . $_SESSION['scenario'];
            
                    if ($resultPath = mysqli_query($conn, $queryPath)) {
                        while($path = mysqli_fetch_array($resultPath)) {
                            $area = substr($path['area'], 0, 120) . "...";
                            $thisId = $path['id'];
                            echo "<option value='$thisId'>$thisId=" .  htmlspecialchars($area) . "</option>";
                        }
                    }
                    echo "</datalist>
                        <button type='button' name='loopLink' class='btn btn-dark form-control mt-2 loopLink submit' id='loopLinkArea' disabled>Loop Link</button>
                        </form>";
                } ?>

            </form>
                    
        </div>
    </div>

<?php if ($tableType == "RPG") {
    echo "<script type='text/javascript' src='rpgmode.js'></script>";
} ?>

<script>

    var id = 0;
    let debug = false;
    <?php if ($username == "Willie") {
        echo "debug = true;";
    } ?>
    let edit = false;
    let view = false;
    let rpg = false;
    let update = false;
    let areaColor = null;
    let data = {};
    let loadedAreas = [];
    let btnMax = <?php echo $btnMax; ?>;
    let table = {<?php echo "name: '" . $_SESSION['scenario'] . "',
        type: '$tableType',
        btnMax: $btnMax,
        rpg: " . ($tableType == "RPG" ? 'true' : 'false') . ",
        edit: " . ($edit ? 'true' : 'false') . ",
        view: " . ($view ? 'true' : 'false');?>};
    let username = "<?php echo $username; ?>";

    if (!table.view) {
        displayError("You don't have access to view this!", "warning");
        $(".main-box").html("You don't have access to view this!<br><a href='browse.php' class='btn btn-warning'>Browse</a>");
    } else if (!table.edit) {
        displayError("View Only", "primary");
        if (username == "anonymous" && table.name == "portal") {
            $("#addPathForm").html(`You don't have access to add to this!<br>Login to be able to edit.<br>
                <button class='restart btn btn-primary'>Restart</button>
                <a type='button' href='index.php' class='btn btn-outline-primary'>Login</a>`);
            $("#tableSelector").addClass("disabled");
            displayError("Login to view other tables", "warning");
        } else {
            $("#addPathForm").html("You don't have access to add to this!<br><button class='restart btn btn-primary'>Restart</button>");
        }
    }
    
    $("#tableSelector").val(table.name);
    loadArea(1,table);

    function loadArea(area,table) {
        let name = table.name;
        console.log("name:", String(name));
        console.log("php size:", <?php echo $btnMax; ?>);
        $("#oldId").val(id);

        //not already loaded
        if (!loadedAreas["area" + area] || loadedAreas["area" + area] == "") {

            $.post("ajax.php?type=load", { id: area, table: name, size: <?php echo $btnMax; ?>}, function(response) {
                console.log("name:", name);
                if (response) {
                    data = JSON.parse(response);
                    loadedAreas["area" + data.id] = data;
                    console.log("loaded new area:", data);
                    renderArea(data);
                } else {
                    console.log("nothing");
                    displayError("no area found", "warning");
                    newPath(0,0,table);
                    return;
                }
            });
            //cached data
        } else {
            data = loadedAreas["area" + area];
            console.log("Loaded area from cache:", data);
            renderArea(data);
        }
    }

    function renderArea(object) {
        id = object.id;
        let buttonHtml = "";
        for (let i = 1; i <= btnMax; i++) {
            if (object[`button${i}`]) {
                buttonHtml += object[`button${i}`];
            }
        }

        if (object.color) {
            $("body").css("--body-bg-color", object.color);
            areaColor = object.color;
        } else {areaColor = null}

        $("#currentArea").val(object.id);
        $("#areaDescription").html(object.area);
        $("#author").html(object.author)

        $("#choiceButtons").html(buttonHtml);

        if (table.rpg) {
            checkScore(object.area, $("#areaDescription"));
            $("#choiceButtons button").each(function(index) {
                // checkAbility($(this).find(".choice-text").text(), $(this));
                checkAbility(object[`choice${$(this).data("choice")}`], $(this));
            })
        }

        let history = `
            <tr data-link='${object.id}'>
                <td class='description'>${object.id}</td>
                <td class='link' data-choice='1'>${object.choice1}</td>
                <td class='link' data-choice='2'>${object.choice2}</td>
                <td class='link' data-choice='3'>${object.choice3}</td>
            </tr>`

        $("#history").append(history);
        // $("#data").html(html);
    }

    function addArea(submitType) {
        let stay = $("#stay").prop("checked");

        //looplink
        if (submitType == "loopLink") {
            let old = $("#oldId").val();
            $.post("ajax.php?type=loop",
                {
                    id: $("#areaId").val(),
                    old: old,
                    choice: $("#oldChoice").val(),
                    table: $("#oldTable").val(),
                    path: $("#pathInput").val(),
                },
                function(response) {
                    // Handle the response from the server
                    console.log(response);
                    delete loadedAreas["area" + old];
                    if (stay) {
                        loadArea(old, $("#oldTable").val());
                    } else {
                        // loadArea(1, $("#oldTable").val());
                        $(".restart").trigger("click");
                    }
                    $("#inputData").hide();
                    $("#data").show();
                    
                }
            )
        } else {
            //add or update
            $.post("ajax.php?type="+submitType,
                {
                    id: $("#areaId").val(),
                    description: $("#area").val(),
                    size: btnMax,
                    <?php for($i = 1; $i <= $btnMax; $i++) {
                        echo "option${i}: $(`#option${i}`).val(),";
                        echo "pathLink${i}: $(`#pathLink${i}`).val(),";
                    }?>
                    // option1: $("#option1").val(),
                    // option2: $("#option2").val(),
                    // option3: $("#option3").val(),
                    // option4: $("#option4").val(),
                    // option5: $("#option5").val(),
                    // option6: $("#option6").val(),
                    areaColor: $("input[name='areaColor']").val(),
                    old: $("#oldId").val(),
                    choice: $("#oldChoice").val(),
                    table: $("#oldTable").val(),
                    // pathLink1: $("#pathLink1").val(),
                    // pathLink2: $("#pathLink2").val(),
                    // pathLink3: $("#pathLink3").val(),
                    // pathLink4: $("#pathLink4").val(),
                    // pathLink5: $("#pathLink5").val(),
                    // pathLink6: $("#pathLink6").val(),
                    author: $("#newPathAuthor").val(),
                },
                function(response) {
                    // Handle the response from the server
                    response = JSON.parse(response);
                    let thisId = response.id;
                    let old = $("#oldId").val();
                    console.log(response);
                    if (submitType == "add") {
                        $("#path").append(`<option value='${thisId}'>${thisId}=${response.description}</option>`);
                        delete loadedAreas["area" + old];
                    } else if (submitType == "update") {
                        $(`#path option[value='${thisId}']`).html(`${thisId}=${response.description}`);
                        delete loadedAreas["area" + thisId];
                        console.log("unloaded area: " + thisId);
                    }
                    if (stay) {
                        loadArea($("#oldId").val(), $("#oldTable").val());
                    } else {
                        loadArea(1, $("#oldTable").val());
                    }
                    $("#inputData").hide();
                    $("#data").show();
                    $(".pathSelect").html("<option value='0' class='default'>Link: <span>0</span></option>")

                    displayError(response.message, "primary");
                }
            
            )}
    }

    $("#addPathForm button.submit").on("click", function() {
        let submitType = $(this).attr("name");
        console.log(submitType);
        addArea(submitType);
    })

    $(".restart").on("click", function() {
        id = 1;
        // let table = $("#tableSelector").val();
        console.log("restart");
        $("#history").html("");
        $("#inputData").hide();
        $("#data").show();
        loadArea(id, table);
    })
    $("#backBtn").on("click", function() {
        $("#inputData").hide();
        $("#data").show();
    })

    $(document).on("click", "#choiceButtons button", function() {
        let value = $(this).data("link");
        // let table = $("#tableSelector").val();
        let choice = $(this).data("choice");
        let text = $(this).html();
        console.log(text);

        // let data = loadedAreas["area" + id];

        if (table.rpg) {
            checkScore(data[`button${choice}`]);
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
        $("#area").val(String(data.area));

        $(".pathSelect").css("display", "block");

        for (let i = 1; i <= btnMax; i++) {
            if (data[`choice${i}`]) {
                $("#option" + i).val(String(data[`choice${i}`]));
            } else {
                $("#option" + i).val("");
            }
            if (data[`link${i}`]) {
                // $("#pathLink" + i + " .default span").html(data[`link${i}`]);
                $("#pathLink" + i).val(data[`link${i}`]);
            }
        }

        $("#newPathAuthor").val($("#author").html());
        $("#oldId").val($("#currentArea").val());
        $("#oldTable").val(table.name);
        $("#inputData").show();
        $("#submit").attr("name", "update");
        $("#submit").text("Update");
        $(".new-path").html("Update path: " + $("#oldId").val());
        $(".colorInput").html("");
        update = true;

        if (areaColor) {
            $(".colorInput").html(`<label for='areaColor'>Color: </label><input type='color' name='areaColor' value='${areaColor}'>`);
            console.log("color: " + areaColor);
            areaColor = false;
        }
    })

    $("#history").on("click", "tr", function() {
        let value = $(this).data("link");
        // let table = $("#tableSelector").val();
        console.log(value);

        loadArea(value, table);
    })

    $("#debugArea").on("change", function() {
        let area = $("#debugArea").val();
        loadArea(area, table);
    })

    function newPath(choice, oldId, table) {
        console.log(oldId);
        $(".new-path").html("New Path Found!");
        if (oldId == 0 || oldId == "undefined") {
            console.log("new adventure");
            $(".prev-choice").hide();
            $(".new-path").html("Beginning of a new adventure: " + table.name);
        }
        $("#addPathForm input:not([type='submit']), #addPathForm textarea").val("");
        $(".default").html("No Path Link");
        $(".default").val(0);

        $("#oldId").val(oldId);
        $("#oldChoice").val(choice);
        $("#oldTable").val(table.name);
        $("#newPathAuthor").val(username);
        $(".colorInput").html("");

        $("#submit").attr("name", "add");
        $("#submit").text("Submit");

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
    
    if (debug) {
        $(":root").css("--debug", "inherit");
        $("#debug-toggle").prop("checked", true);

    }

    function displayError(message, type) {
        if (!message || message == "" || message == "undefined") {
            return;
        }

        let buttonHtml = "";
        if (type != "primary" || true) {
            buttonHtml = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
        }

        let thisDiv = $(`<div class="alert alert-${type} error">${message}${buttonHtml}</div>`);
        $("#errorHolder").append(thisDiv);
        setTimeout(() => {
            thisDiv.remove();
        }, 3000);
    }

    displayError(<?php echo "`$error`, '$errorType'";?>);

    //Change theme
    $(document).on("change", "#themeSelect", function() {
        let theme = $(this).val();
        $("body").attr("data-theme", theme);
    });

    //only activate loop link button on change
    $(document).on("change", "#path, #pathInput", function() {
        $("#loopLinkArea").attr("disabled", false);
    });

    //loop link path
    $(document).on("click", ".loopLinkPath", function() {
        let html = $(this).html();
        let clicked = $(this);
        let buttonNumber = $(this).data("loop");
        console.log(buttonNumber);
        
        if (html == "Loop Link") {
            if ($("#pathLink" + buttonNumber + " option").length < 2) {
                // $("#pathLink" + buttonNumber).append($("#path").html());
            }
            $("#pathLink" + buttonNumber).show();
            clicked.html("Remove Link");
        
        } else if (!update){
            $("#pathLink" + buttonNumber).hide();
            $("#pathLink" + buttonNumber).val(0);
            $(this).html("Loop Link");
        }
        if (update) {
            $(this).html("Loop Link");
            // $(this).addClass("disabled");
        }
    })

    <?php if ($tableType != "Classic") {echo "
    $('.loopLinkPath').css('display', 'block');
    ";}?>

    loadedAreas['areawin'] = {
        id: "areawin",
        area: "You win! Thanks for playing. There's probably more to the game if you want to keep playing. Otherwise there's more adventures just waiting for you. If you enjoyed it, consider leaving a review.",
        button1: "<button class='btn btn-pathWin col-md' data-choice='1' data-link='1'>Play Again</button>",
        button2: "<a class='btn btn-warning col-md' href='browse.php'>Find new adventure</a><a class='btn btn-info col-md' href='reviews.php'>Leave a review</a>"
    }

</script>



</body>
</html>