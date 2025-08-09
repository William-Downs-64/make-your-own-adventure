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
                <a href="new.php" class="btn btn-secondary">Play</a>
                <input type="submit" name="logOut" value="logout" class="btn btn-outline-primary">
            </form>';
} else {
    echo "Not Logged in!";
    echo '<div id="login" class="float-end">
            <a type="button" href="/login" class="btn btn-outline-primary">Login</a>
        </div>';
}

// if (array_key_exists('scenario', $_GET)) {
//     $_SESSION['scenario'] = $_GET['scenario'];
// }

//if edit table data is sent
if (array_key_exists('saveEdit', $_POST) && array_key_exists('tableName', $_POST)) {
    $string = "UPDATE `tables` SET `description` = '" . mysqli_real_escape_string($conn, $_POST['description']) . 
                "', `viewer` = '" . mysqli_real_escape_string($conn, $_POST['viewerList']) .
                "', `editor` = '" . mysqli_real_escape_string($conn, $_POST['editorList']) .
                "' WHERE `creator` = '$username' AND `name` = '" . $_POST['tableName'] . "'";
    mysqli_query($conn, $string);
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

    <link rel="stylesheet" href="style.css">

</head>
<body>

    <div id="editTable" class="d-none">
        <form method="post" class="container row">
            <div class="">
                <label for="editTableName" class="col-3 h2">Edit Table: <span class="table-name"></span></label>
                <input type="hidden" id="editTableName" name="tableName" class="hidden" readonly>
            </div>
            <div>View Priviledges:
                <input id="viewers" name="viewerList" readonly class="form-control">
                <br>
                <input type="text" id="addViewer" placeholder="Add Viewer">
                <button type="button" id="viewerButton">Add</button>
                <input type="text" id="removeViewer" placeholder="Remove Viewer">
                <button type="button" id="viewerButton2">Remove</button>
                <button type="button" id="viewerButtonNone">None</button>
                <button type="button" id="viewerButtonAll">Public</button>
            </div>
            <div>Edit Priviledges:
                <input id="editors" name="editorList" readonly class="form-control">
                <br>
                <input type="text" id="addEditor" placeholder="Add Editor">
                <button type="button" id="editorButton">Add</button>
                <input type="text" id="removeEditor" placeholder="Remove Editor">
                <button type="button" id="editorButton2">Remove</button>
                <button type="button" id="editorButtonNone">None</button>
                <button type="button" id="editorButtonAll">Public</button>
            </div>
            <label for="description" class="col-2">Edit Description:</label>
            <input class="form-control col-10" name="description" id="description">
            <input type="submit" value="save" name="saveEdit" class="btn btn-primary mt-2">
        </form>
    </div>

    <div id="myOwnTables">
        <h3>My Created Tables</h3>
        <a href="createNew.php" class="btn btn-info">Create New Table</a>
        <div class="table-container container">

        </div>
        
    </div>

    <div id="myTables">
        <h3>My Tables</h3>
        <div class="table-container container">
            <div class="card adventure-card m-2" id="portal">
                <div class="card-body">
                    <h4 class="card-title">Portal</h4>
                    <p>Description: The classic Make Your Own Adventure where anything can happen</p>
                    <p>Owner: WieRD</p>
                    <p>Editable: Yes</p>
                    <div class="buttonArea">
                        <button class="btn btn-danger" disabled>Immovable</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="browseTables" class="bg-success">
        <h3>All Tables</h3>
        <div class="table-container container">
        
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

    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }

    class Adventure {
        constructor(thatRow, location) {
            this.name = thatRow['name'];
            this.description = thatRow['description'];
            this.owner = thatRow['creator'];
            this.location = location;
            this.editors = thatRow['editor'];
            this.viewers = thatRow['viewer'];
            this.button = `<button class='btn btn-primary'>Error</button>`;
        }

        get _button() {
            this.button = "<button class='btn btn-primary switch'>Add</button>";
            if (this.location == "myTables") {
                this.button = "<button class='btn btn-danger switch'>Remove</button>";
            }
            if (this.location == "myOwnTables") {
                this.button = "<button class='btn btn-success edit'>Edit Table</button>";
            }
            return this.button;
        }

        set _location(newLocation) {
            this.location = newLocation;
            $(`#${newLocation} .table-container`).append($("#" + this.name));
        }


        get load() {

            // let buttonHTML = this.button;
            if (!$(`#${this.name}`).length) {
                let spaceName = this.name.replaceAll("_", " ");
                let edit = "No";
    
                if (this.owner == username || this.editors.includes(`-(${username})`) || this.editors.includes("-all")) {
                    edit = "Yes";
                }

                $(`#${this.location} .table-container`).append(`
                    <div class="card adventure-card m-2" id="${this.name}">
                        <div class="card-body">
                            <h4 class="card-title">${spaceName}</h4>
                            <p>Description: ${this.description}</p>
                            <p>Owner: ${this.owner}</p>
                            <p>Editable: ${edit}</p>
                            <div class="buttonArea">
                                ${this._button}
                            </div>
                        </div>
                    </div>
                
                `)
            }
        }

    }


    let myTables = [];

    <?php
        $result = mysqli_query($conn, "SELECT * FROM `tables` WHERE `creator` = '$username'");
        while ($row = mysqli_fetch_array($result)) {
            $js_row = json_encode($row);
            echo "myTables['" . $row['name'] . "'] = new Adventure($js_row ,'myOwnTables');";
            echo "myTables['" . $row['name'] . "'].load;";
        }
        
        
        
        $string = "SELECT `myTables` FROM `users` WHERE `username` = '$username'";
        $result = mysqli_query($conn, $string);
        $row = mysqli_fetch_array($result);
        $tableArray = explode("-", preg_replace("/[()]/", "", $row['myTables']));
        
        foreach ($tableArray as $value) {
            // $value = preg_replace("/[()]/", "", $value);
            $sql = "SELECT * FROM `tables` WHERE `name` = '$value'";
            $result = mysqli_query($conn, $sql);
            if ($row = mysqli_fetch_array($result)){
                $js_row = json_encode($row);
                echo "myTables['" . $row['name'] . "'] = new Adventure($js_row , 'myTables');";
                echo "myTables['" . $row['name'] . "'].load;";
            };

        }
        
        $string = "SELECT * FROM `tables` WHERE `creator` != '$username' AND `viewer` = '-all' OR `viewer` LIKE '%-($username)%'";
        $result = mysqli_query($conn, $string);
        while ($row = mysqli_fetch_array($result)) {
            if (!in_array($row['name'], $tableArray)) {
                $js_row = json_encode($row);
                echo "myTables['" . $row['name'] . "'] = new Adventure($js_row , 'browseTables');";
                echo "myTables['" . $row['name'] . "'].load;";
            };

        }
    ?>

    $(document).on("click", "#browseTables button.switch", function() {
        let move = $(this).parents(".adventure-card");
        let thisTable = move.attr("id");
        console.log(thisTable);
        console.log(move);


        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                myTables[thisTable]._location = "myTables";
                $(`#${thisTable} .buttonArea`).html(myTables[thisTable]._button);
            }
        }
        console.log("updating data");
        xmlhttp.open("GET",`ajax.php?table=${thisTable}&type=myTables&function=add`,true);
        xmlhttp.send();

    })
    $(document).on("click", "#myTables button.switch", function() {
        let move = $(this).parents(".adventure-card");
        let thisTable = move.attr("id");
        console.log(thisTable);
        console.log(move);


        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                myTables[thisTable]._location = "browseTables";
                $(`#${thisTable} .buttonArea`).html(myTables[thisTable]._button);
            }
        }
        console.log("updating data");
        xmlhttp.open("GET",`ajax.php?table=${thisTable}&type=myTables&function=remove`,true);
        xmlhttp.send();

    })
    
    $(document).on("click", "#myOwnTables button.edit", function() {
        let edit = $(this).parents(".adventure-card");
        let thisTable = myTables[edit.attr("id")];

        $("#editTable").removeClass("d-none");

        $("#editTableName").val(thisTable.name);
        $(".table-name").html(thisTable.name);
        $("#editors").val(thisTable.editors);
        $("#viewers").val(thisTable.viewers);
        $("#description").val(thisTable.description);

    })

    //add and remove viewers for the edit table
    $("#viewerButton").on("click", function() {
        if ($("#addViewer").val().length > 0) {
            $("#viewers").val($("#viewers").val()+"-("+$("#addViewer").val()+")");
            $("#addViewer").val("");
        }
    })
    $("#viewerButton2").on("click", function() {
        let value = $("#viewers").val().replace(`-(${$("#removeViewer").val()})`, "")
        $("#viewers").val(value);
        $("#removeViewer").val("");
    })
    $("#viewerButtonAll").on("click", function() {
        $("#viewers").val("-all");
    })
    $("#viewerButtonNone").on("click", function() {
        $("#viewers").val(`-(${username}`);
        $("#editors").val(`-(${username}`);
    })

    //add and remove editors for the edit table
    $("#editorButton").on("click", function() {
        if ($("#addEditor").val().length > 0) {
            $("#editors").val($("#editors").val()+"-("+$("#addEditor").val()+")");
            $("#addEditor").val("");
        }
    })
    $("#editorButton2").on("click", function() {
        let value = $("#editors").val().replace(`-(${$("#removeEditor").val()})`, "")
        $("#editors").val(value);
        $("#removeEditor").val("");
    })
    $("#editorButtonAll").on("click", function() {
        $("#editors").val("-all");
    })
    $("#editorButtonNone").on("click", function() {
        $("#editors").val(`-(${username}`);
    })

</script>

</body>
</html>