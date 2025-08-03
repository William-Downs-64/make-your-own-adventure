<?php
    session_start();

    if(array_key_exists("id", $_SESSION) OR array_key_exists("id", $_COOKIE)) {
        header("Location: ../new.php");
    }

    $error = "";

    if($_POST) {

        $error = "";

        include('connection.php');

        //sign up
        if (isset($_POST['submit1'])){

            $query = "SELECT username FROM users WHERE username = '"
                    . mysqli_real_escape_string($conn, $_POST['usernameSignUp']) . "'";
                        

            if($result = mysqli_query($conn, $query)) {
                if($row = mysqli_fetch_array($result)) {
                    $error .= "Error: That username is already used!";
                }
                else {
                    
                    $hash = password_hash(mysqli_real_escape_string($conn, $_POST['passwordSignUp']), PASSWORD_DEFAULT);


                    $queryInsert = "INSERT INTO users (username, password) VALUES ('"
                    . mysqli_real_escape_string($conn, $_POST['usernameSignUp']) . "', '"
                    . $hash . "')";
                    
                    mysqli_query($conn, $queryInsert);



                    if($_POST['staySignUp']) {
                        setcookie("id", $_POST['usernameSignUp'], time() + 60 * 60 * 24 * 7);
                    }

                    $_SESSION['user'] = $_POST['usernameSignUp'];

                    header('Location: ../new.php');
                    
                }
            }
        }

        //login
        if (isset($_POST['submit2'])){
           
            $query = "SELECT * FROM users WHERE username = '"
                    . mysqli_real_escape_string($conn, $_POST['usernameLogin']) . "'";

            if($result = mysqli_query($conn, $query)) {
                if($row = mysqli_fetch_array($result)) {
                
                    if(password_verify($_POST['passwordLogin'], $row['password'])) {
                        echo "The password is valid!";
                        $_SESSION['user'] = $_POST['usernameLogin'];
                        // $_SESSION[''] = $row['code'];

                        if($_POST['stayLogin']) {
                            setcookie("id", $_POST['usernameLogin'], time() + 60 * 60 * 24);
                        }
                        
                        header('Location: ../new.php');
        
                    }
                    else {
                        $error .= "The username or password is invalid";
                    }

                }
                else {

                    $error .= "That username or password is invalid.<br>";
                }
            }
            else {

                $error .= "connection error";
            }         
        }
    }
?>

<!DOCTYPE html>
<html>

    <head>

    <meta charset="utf-8" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <script
        src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
          crossorigin="anonymous"></script>

    <title>Underwater Missions</title>

    <style type="text/css">

        body {
            background-color: blue;
        }

        #signUp {
            display: none;
        }

        #head {
            width: 400px;
            background-color: lightblue;
            border: 2px black solid;
            border-radius: 5px;
        }

        .toggleForms {
            color: black;
            font-weight: bold;
        }
    </style>

    </head>

    <body>

        <div class="text-center mt-4 mx-auto p-4" id="head">
            <h1>Underwater Missions</h1>

            <p>Support missionaries or become one yourself!</p>
        
        </div>  

        <div class="text-center col-sm-6 col-lg-4 mx-auto mt-3 bg-primary p-3">
        <div id="error" class="alert alert-danger"><?php echo $error; ?></div>
            <form method="post" id="signUp">

                <p>Interested? Sign up now!</p>
                <input type="name" name="usernameSignUp" id="usernameSignUp" class="form-control" placeholder="Your username">
                <input type="password" name="passwordSignUp" id="passwordSignUp" class="form-control" placeholder="Password">
                <input type="checkbox" name="staySignUp">
                <label for="staySignUp">Stay Logged in?</label><br>
                <input type="submit" name="submit1" id="submit1" value="Sign Up" class="btn btn-secondary">
                <p><a class="toggleForms">Log In</a></p>

            </form>

            <form method="post" id="login">

                <p>Log in</p>
                <input type="name" name="usernameLogin" id="usernameLogin" class="form-control" placeholder="Your username">
                <input type="password" name="passwordLogin" id="passwordLogin" class="form-control" placeholder="Password">
                
                <fieldset class="checkbox">
                    <input type="checkbox" name="stayLogin">
                    <label for="stayLogin">Stay Logged in?</label><br>
                </fieldset>

                
                <input type="submit" name="submit2" id="submit2" value="Login" class="btn btn-secondary">
                <p><a class="toggleForms">Sign Up</a></p>

            </form>
        </div>

        <script type="text/javascript">

            let error = "";

            //sign up
            $("#signUp").submit(function(e) {

                let error = "";

                if ($("#usernameSignUp").val() == ""){
                    error += "username is required!<br>";
                }

                if ($("#passwordSignUp").val() == "") {
                    error += "Password is required!<br>";
                }

                if (error) {
                    error = "An Error occurred!<br>" + error;
                    $("#error").html(error);
                    return false;
                } else {
                    return true;
                }
            })

            //log in
            $("#login").submit(function(e) {

                let error = "";

                if ($("#usernameLogin").val() == ""){
                    error += "username is required!<br>";
                }

                if ($("#passwordLogin").val() == "") {
                    error += "Password is required!<br>";
                }
                if (error) {
                    error = "An Error occurred!<br>" + error;
                    $("#error").html(error);
                    return false;
                } else {
                    return true;
                }
            })

            $(".toggleForms").click(function() {

                $("#login").toggle();
                $("#signUp").toggle();
            })

            
        </script>

    </body>
</html>