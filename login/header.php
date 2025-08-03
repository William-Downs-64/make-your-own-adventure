<!DOCTYPE html>
<html>
    <head>
        <title>Underwater Missions</title>
    
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

        <style>

            body {
                background: rgb(2,0,36);
                background: linear-gradient(270deg, rgba(2,0,36,1) 
                0%, rgba(9,121,18,1) 42%, rgba(0,212,255,1) 100%);
                width: 100%;
                height: 100%;
            }

            #logout {
                float: right;
            }

            #support{
                padding: 10px;
                width: 600px;
                margin: 60px auto;
                background-color: lightgrey;
            }

            
        </style>
    
    </head>

    <body>
        <nav class="bg-info fixed-top p-1">
        
            <form method="post" id="logout">
                <input type="submit" name="logOut" value="logout" class="btn btn-outline-primary">
            </form>    
            <a href="main.php"> Underwater Missions</a>    
        </nav>