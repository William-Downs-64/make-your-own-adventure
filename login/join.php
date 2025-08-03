<?php
    session_start();
    
    if(array_key_exists('logOut', $_POST)) {
        session_unset();
        
        setcookie("id", "", time() - 60 * 60);

        echo "logging Out";
        header('Location: index.php');
    }

    include("header.php")
?>


    <div id="support">
        <h1>Join the Mission</h1>
    </div>
    <div class="alert alert-info text-center">
        <p> We need your help to reach the underwater dolphins.
            Also we do underwater basket weaving in the Philipines.
            Will you join us and become a missionary with
            Underwater Missions?
        </p>

        <a class="btn btn-secondary" role="button">Yes</a>
        <a class="btn btn-light" href="main.php" role="button">No</a>
    </div>

    <form id="joinForm" method="POST">
        <label for="code">Choose your Rep Code:</label>
        <input id="code" type="number" name="code">
        <input type="submit">

    </form>


    <script type="text/javascript">

        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }

    </script>
</body>
</html>