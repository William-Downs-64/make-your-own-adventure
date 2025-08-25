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
    $_SESSION['user'] = $_COOKIE["id"];
}

if (array_key_exists('scenario', $_GET)) {
    if ($_GET['scenario'] == "New Table") {
        header('location: createNew.php');
    }
    $_SESSION['scenario'] = $_GET['scenario'];
}

if (!array_key_exists('scenario', $_SESSION)) {
    $_SESSION['scenario'] = "portal";
}
$scenario = $_SESSION['scenario'];

$username = "anonymous";
if (array_key_exists('user', $_SESSION)) {
    $username = $_SESSION['user'];
    echo "Welcome: $username";
    echo '<form method="post" id="logout" class="float-end">
                <a href="browse.php" class="btn btn-warning">Browse Tables</a>
                <a href="new.php" class="btn btn-secondary">Play</a>
                <input type="submit" name="logOut" value="logout" class="btn btn-outline-primary">
            </form>';
} else {
    echo "Not Logged in!";
    echo '<div id="login" class="float-end">
            <a type="button" href="index.php" class="btn btn-outline-primary">Login</a>
        </div>';
}

//add review
if(array_key_exists("addReview", $_POST)) {
    $comment = $_POST['comment'];
    $stars = $_POST['stars'];
    $adventure = $_POST['adventure'];
    $user = $_POST['user'];

    $queryInsert = "INSERT INTO reviews (comment, stars, adventure, user) VALUES ('"
        . mysqli_real_escape_string($conn, $comment) . "', '"
        . mysqli_real_escape_string($conn, $stars) . "', '"
        . mysqli_real_escape_string($conn, $adventure) . "', '"
        . mysqli_real_escape_string($conn, $user) . "')";

    mysqli_query($conn, $queryInsert);
}

//edit review
if(array_key_exists("editReview", $_POST)) {
    $comment = $_POST['comment'];
    $stars = $_POST['stars'];
    $adventure = $_POST['adventure'];
    $user = $_POST['user'];
    $id = $_POST['id'];

    $queryUpdate = "UPDATE reviews SET comment = '"
        . mysqli_real_escape_string($conn, $comment) . "', stars = '"
        . mysqli_real_escape_string($conn, $stars) . "', adventure = '"
        . mysqli_real_escape_string($conn, $adventure) . "', user = '"
        . mysqli_real_escape_string($conn, $user) . "' WHERE id = "
        . mysqli_real_escape_string($conn, $id);

    mysqli_query($conn, $queryUpdate);
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script type="text/javascript" src="jquery-3.7.1.min.js"></script>

    <!-- Font Awesome Icon Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

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
    <form method="get">
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
        <button id="search" class="btn btn-outline-dark restart">Search</button>
    </form>
    
    <div class="container">
        <h2>Reviews for: <span class="table-name"></span></h2>

        <div id="commentSection" class="table-container">
            <!-- Reviews go here -->
        </div>
    </div>

    <div class="newReview container">
        <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#reviewForm">Add a Review</button>
        <form method="post" class="collapse" id="reviewForm">

            <h3 id="reviewFormTitle">Add a Review for: <span class="table-name">None Selected</span></h3>
            <label for="comment">Comment:</label>
            <textarea name="comment" id="comment" class="form-control" required></textarea>
            
            <label for="stars">Stars (0.5-5):</label>
            <div class="starHolder edit">
                <div class="stars"></div>
                <div class="stars fill"></div>
            </div>
            <input type="number" id="stars" name="stars" class="form-control" min="0.5" max="5" step="0.5" required>
            <input type="hidden" name="adventure" value="<?php echo $scenario; ?>">
            <input type="hidden" name="user" value="<?php echo $username; ?>">
            <input type="hidden" name="id" value="0" id="reviewID">
            
            <input type="submit" name="addReview" value="Submit Review" class="btn btn-primary mt-2" id="submitReview">
        </form>
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

    $(".table-name").text(table.replaceAll("_", " "));

    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }

    class Review {
        constructor(thatRow, location) {
            this.id = thatRow['id'];
            this.table = thatRow['table'];
            this.comment = thatRow['comment'];
            this.owner = thatRow['user'];
            this.location = commentSection;
            this.stars = thatRow['stars'];
            this.starsPercent = (this.stars / 5) * 100;
            this.time = thatRow['time'];
            this.button = "";
            if (this.owner == username) {
                this.button = `<button class="btn btn-danger edit">Edit</button>`;
            }
        }


        set _location(newLocation) {
            this.location = newLocation;
            $(`#${newLocation} .table-container`).append($("#" + this.name));
        }


        get load() {

            // let buttonHTML = this.button;
            if (!$(`#${this.name}`).length) {
                let edit = "No";
    
                $(`#commentSection`).append(`
                    <div class="card review-card m-2" id="${this.id}">
                        
                        <div class="card-header">
                            <h4 class="card-title">${this.owner}</h4>
                            <div class="starHolder">
                                <div class="stars"></div>
                                <div class="stars fill" style="width: ${this.starsPercent}%;"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Comment: ${this.comment}</p>
                            <p>Written By: ${this.owner}</p>
                            <p>Stars: ${this.stars}</p>
                            <p>Time: ${this.time}</p>
                            ${this.button}
                        </div>
                    </div>
                
                `)
            }
        }

    }


    let reviewList = [];

    <?php
        $result = mysqli_query($conn, "SELECT * FROM `reviews` WHERE `adventure` = '$scenario'");
        while ($row = mysqli_fetch_array($result)) {
            $js_row = json_encode($row);
            echo "reviewList['" . $row['id'] . "'] = new Review($js_row ,'commentSection');";
            echo "reviewList['" . $row['id'] . "'].load;";
        }
    ?>
    

    //dropdown cards on click
    $(document).on("click", ".review-card .card-header", function() {
        let cardBody = $(this).siblings(".card-body");
        cardBody.toggleClass("show");
        // cardBody.css("height", cardBody.hasClass("show") ? "fit-content" : "0px");
    });

    //edit review
    $(document).on("click", ".review-card button.edit", function() {
        let edit = $(this).parents(".review-card");
        let thisTable = reviewList[edit.attr("id")];

        $("#stars").val(thisTable.stars);
        $("#comment").val(thisTable.comment);
        $("#submitReview").attr("name", "editReview");
        $("#submitReview").val("Update Review");
        $("#reviewFormTitle").text("Edit Review for: " + thisTable.name);
        $("#reviewID").val(thisTable.id);

        $("#reviewForm").collapse("show");
    })

    //star functionality
    let stars = 3;
    let starWidth = (stars / 5) * 100;
    $(".starHolder.edit .stars.fill").css("width", starWidth + "%");

    $(".starHolder.edit").on("mousemove", function(e) {
        let offset = $(this).offset();
        let relativeX = e.pageX - offset.left;
        let totalWidth = $(this).width();
        let percent = relativeX / totalWidth;
        let fillWidth = Math.min(Math.max(percent, 0), 1) * 100;
        fillWidth = Math.round(fillWidth / 10 + .5) * 10;
        $(".starHolder.edit .stars.fill").css("width", fillWidth + "%");
    });
    $(".starHolder.edit").on("mouseleave", function() {
        $(".starHolder.edit .stars.fill").css("width", $("#stars").val() / 5 * 100 + "%");
    });
    $(".starHolder.edit").on("click", function(e) {
        let offset = $(this).offset();
        let relativeX = e.pageX - offset.left;
        let totalWidth = $(this).width();
        let percent = relativeX / totalWidth;
        stars = Math.min(Math.max(percent, 0), 1) * 10;
        stars = Math.round(stars + 0.5) * 0.5;
        let fillWidth = (stars / 5) * 100;
        $(".starHolder.edit .stars.fill").css("width", fillWidth + "%");
        console.log("Selected stars: " + stars);
        $("#stars").val(stars);
    });

    $("#stars").on("change", function() {
        let stars = $(this).val();
        let fillWidth = (stars / 5) * 100;
        $(".starHolder.edit .stars.fill").css("width", fillWidth + "%");
    });
</script>

</body>
</html>