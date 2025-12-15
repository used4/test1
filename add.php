<?php
$storageDir = __DIR__ . '/tags';


if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}


$requestUri = $_SERVER['REQUEST_URI']; 
$scriptName = $_SERVER['SCRIPT_NAME']; 
$tagPath = trim(str_replace($scriptName, '', parse_url($requestUri, PHP_URL_PATH)), '/');
$tag = preg_replace('/[^a-zA-Z0-9_-]/', '', $tagPath); // sanitize


$filePath = $storageDir . '/' . $tag . '.txt';


if (isset($_POST['submit'])) {
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $tagPath = trim(str_replace($scriptName, '', parse_url($requestUri, PHP_URL_PATH)), '/');
    $tag = preg_replace('/[^a-zA-Z0-9_-]/', '', $tagPath); // Sanitize tag

    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']); 
    $baseUrl = "$protocol://$host$scriptDir/add.php";

    
    $url = "$baseUrl/" . urlencode($tag) . "?save&username=" . urlencode($username) . "&password=" . urlencode($password)."&status=ok";

    
    echo "<script>window.location.href='$url';</script>";
    exit; 
}

if (isset($_GET['save'])) { 
    
    $password = isset($_GET['password']) ? trim($_GET['password']) : ''; 
    $username =isset($_GET['username']) ? trim($_GET['username']) : ''; 
    
    $data = [ 'password' => $password, 'username' => $username ];
    
    if ($username == '' || $password == '') { 
        echo displayErrorPage(); 
        exit; 
    } 
    file_put_contents($filePath, json_encode($data)); 
    echo displaySuccessPage();
    exit; 
}


function displaySuccessPage() {
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>
        body {
            text-align: center;
            padding: 40px 0;
            background: #EBF0F5;
        }
        h1 {
            color: #88B04B;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-weight: 900;
            font-size: 40px;
            margin-bottom: 10px;
        }
        p {
            color: #404F5E;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-size: 20px;
            margin: 0;
        }
        i {
            color: #9ABC66;
            font-size: 100px;
            line-height: 200px;
            margin-left: -15px;
        }
        .card {
            background: white;
            padding: 60px;
            border-radius: 4px;
            box-shadow: 0 2px 3px #C8D0D8;
            display: inline-block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="card">
        <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto;">
            <i class="checkmark">✓</i>
        </div>
        <h1>Success</h1>
        <p>Your details have been sent to the app.<br/>Please wait a moment until the progress bar in the app is complete.</p>
    </div>
</body>
</html>
HTML;

    return $html;
}


function displayErrorPage() {
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <style>
        body {
            text-align: center;
            padding: 40px 0;
            background: #EBF0F5;
        }
        h1 {
            color: #eb4034;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-weight: 900;
            font-size: 40px;
            margin-bottom: 10px;
        }
        p {
            color: #404F5E;
            font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
            font-size: 20px;
            margin: 0;
        }
        i {
            color: #eb4034;
            font-size: 100px;
            line-height: 200px;
            margin-left: -15px;
        }
        .card {
            background: white;
            padding: 60px;
            border-radius: 4px;
            box-shadow: 0 2px 3px #C8D0D8;
            display: inline-block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="card">
        <div style="border-radius:200px; height:200px; width:200px; background: #FFEBEB; margin:0 auto;">
            <i class="checkmark">⚠︎</i>
        </div>
        <h1>Warning</h1>
        <p>There is something wrong..<br/>Your details were not sent to the app correctly..</p>
    </div>
</body>
</html>
HTML;

    return $html;
}


if (isset($_GET['get'])) {
    header('Content-Type: application/json');

    if (file_exists($filePath)) {
        $json = file_get_contents($filePath); // Read JSON as string
        $data = json_decode($json, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "success",
                "data"   => $data
            ]);
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => "Invalid JSON format in TAG {$tag}"
            ]);
        }
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "TAG {$tag} not found."
        ]);
    }
    exit;
}

if (isset($_GET['delete'])) {
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "TAG {$tag} deleted.";
    } else {
        echo "TAG {$tag} not found.";
    }
    exit;
}

if (isset($_GET['deleteall'])) {
    $storageDir = __DIR__ . '/tags';
    deleteTxtFiles($storageDir);
    exit;
}

function deleteTxtFiles($storageDir) {
    $storageDir = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (!is_dir($storageDir)) {
        echo "Directory does not exist: $storageDir\n";
        return false;
    }
    $files = glob($storageDir . '*.txt');
    if (empty($files)) {
        echo "No TAG files found";
        return false;
    }
    $deletedCount = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            if (unlink($file)) {
                $deletedCount++;
            }
        }
    }
    echo "Deleted $deletedCount TAG files";
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Playlist إضافة قائمة تشغيل</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }
        :root {
            --bg-color: #f0f0f0;
            --text-color: #333;
            --container-bg: #fff;
            --container-border: #ccc;
            --button-bg: #007bff;
            --button-hover: #0056b3;
        }
        body.dark-mode {
            --bg-color: #222;
            --text-color: #fff;
            --container-bg: #333;
            --container-border: #555;
            --button-bg: #1e90ff;
            --button-hover: #1c86ee;
        }
        .container {
            text-align: center;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            background-color: var(--container-bg);
            border: 2px solid var(--container-border);
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            margin-top: 60px;
        }
        .logo {
            width: 90%;
            height: auto;
            margin: 0 auto 20px auto;
            display: none;
        }
        .logo.light {
            display: block;
        }
        body.dark-mode .logo.light {
            display: none;
        }
        body.dark-mode .logo.dark {
            display: block;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid var(--container-border);
            border-radius: 5px;
            box-sizing: border-box;
            background-color: var(--container-bg);
            color: var(--text-color);
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: var(--button-bg);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: var(--button-hover);
        }
        .theme-toggle {
            position: fixed;
            top: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            background-color: var(--button-bg);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .theme-toggle:hover {
            background-color: var(--button-hover);
        }
        .theme-toggle .fa-sun {
            display: none;
        }
        body.dark-mode .theme-toggle .fa-sun {
            display: inline;
        }
        body.dark-mode .theme-toggle .fa-moon {
            display: none;
        }
        .copyright {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 14px;
            color: var(--text-color);
        }
        @media screen and (max-width: 600px) {
            .container {
                margin-top: 80px;
                padding: 15px;
                width: 90%;
            }
            .theme-toggle {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }
        }
        .custom-btn {
          width: 100%;
          height: 40px;
          color: #fff;
          border-radius: 5px;
          padding: 10px 25px;
          font-family: 'Lato', sans-serif;
          font-weight: 500;
          background: transparent;
          cursor: pointer;
          transition: all 0.3s ease;
          position: relative;
          display: inline-block;
           box-shadow:inset 2px 2px 2px 0px rgba(255,255,255,.5),
           7px 7px 20px 0px rgba(0,0,0,.1),
           4px 4px 5px 0px rgba(0,0,0,.1);
          outline: none;
        }
        
        /* 1 */
        .btn-1 {
          background: rgb(6,14,131);
          background: linear-gradient(0deg, rgba(6,14,131,1) 0%, rgba(12,25,180,1) 100%);
          border: none;
        }
        .btn-1:hover {
           background: rgb(0,3,255);
        background: linear-gradient(0deg, rgba(0,3,255,1) 0%, rgba(2,126,251,1) 100%);
        }
        
        /* 2 */
        .btn-2 {
          background: rgb(96,9,240);
          background: linear-gradient(0deg, rgba(96,9,240,1) 0%, rgba(129,5,240,1) 100%);
          border: none;
          
        }
        .btn-2:before {
          height: 0%;
          width: 2px;
        }
        .btn-2:hover {
          box-shadow:  4px 4px 6px 0 rgba(255,255,255,.5),
                      -4px -4px 6px 0 rgba(116, 125, 136, .5), 
            inset -4px -4px 6px 0 rgba(255,255,255,.2),
            inset 4px 4px 6px 0 rgba(0, 0, 0, .4);
        }
        
        
        /* 3 */
        .btn-3 {
          background: rgb(0,172,238);
        background: linear-gradient(0deg, rgba(0,172,238,1) 0%, rgba(2,126,251,1) 100%);
          width: 130px;
          height: 40px;
          line-height: 42px;
          padding: 0;
          border: none;
          
        }
        .btn-3 span {
          position: relative;
          display: block;
          width: 100%;
          height: 100%;
        }
        .btn-3:before,
        .btn-3:after {
          position: absolute;
          content: "";
          right: 0;
          top: 0;
           background: rgba(2,126,251,1);
          transition: all 0.3s ease;
        }
        .btn-3:before {
          height: 0%;
          width: 2px;
        }
        .btn-3:after {
          width: 0%;
          height: 2px;
        }
        .btn-3:hover{
           background: transparent;
          box-shadow: none;
        }
        .btn-3:hover:before {
          height: 100%;
        }
        .btn-3:hover:after {
          width: 100%;
        }
        .btn-3 span:hover{
           color: rgba(2,126,251,1);
        }
        .btn-3 span:before,
        .btn-3 span:after {
          position: absolute;
          content: "";
          left: 0;
          bottom: 0;
           background: rgba(2,126,251,1);
          transition: all 0.3s ease;
        }
        .btn-3 span:before {
          width: 2px;
          height: 0%;
        }
        .btn-3 span:after {
          width: 0%;
          height: 2px;
        }
        .btn-3 span:hover:before {
          height: 100%;
        }
        .btn-3 span:hover:after {
          width: 100%;
        }
        
        /* 4 */
        .btn-4 {
          background-color: #4dccc6;
        background-image: linear-gradient(315deg, #4dccc6 0%, #96e4df 74%);
          line-height: 42px;
          padding: 0;
          border: none;
        }
        .btn-4:hover{
          background-color: #89d8d3;
        background-image: linear-gradient(315deg, #89d8d3 0%, #03c8a8 74%);
        }
        .btn-4 span {
          position: relative;
          display: block;
          width: 100%;
          height: 100%;
        }
        .btn-4:before,
        .btn-4:after {
          position: absolute;
          content: "";
          right: 0;
          top: 0;
           box-shadow:  4px 4px 6px 0 rgba(255,255,255,.9),
                      -4px -4px 6px 0 rgba(116, 125, 136, .2), 
            inset -4px -4px 6px 0 rgba(255,255,255,.9),
            inset 4px 4px 6px 0 rgba(116, 125, 136, .3);
          transition: all 0.3s ease;
        }
        .btn-4:before {
          height: 0%;
          width: .1px;
        }
        .btn-4:after {
          width: 0%;
          height: .1px;
        }
        .btn-4:hover:before {
          height: 100%;
        }
        .btn-4:hover:after {
          width: 100%;
        }
        .btn-4 span:before,
        .btn-4 span:after {
          position: absolute;
          content: "";
          left: 0;
          bottom: 0;
          box-shadow:  4px 4px 6px 0 rgba(255,255,255,.9),
                      -4px -4px 6px 0 rgba(116, 125, 136, .2), 
            inset -4px -4px 6px 0 rgba(255,255,255,.9),
            inset 4px 4px 6px 0 rgba(116, 125, 136, .3);
          transition: all 0.3s ease;
        }
        .btn-4 span:before {
          width: .1px;
          height: 0%;
        }
        .btn-4 span:after {
          width: 0%;
          height: .1px;
        }
        .btn-4 span:hover:before {
          height: 100%;
        }
        .btn-4 span:hover:after {
          width: 100%;
        }
        
        /* 5 */
        .btn-5 {
          width: 130px;
          height: 40px;
          line-height: 42px;
          padding: 0;
          border: none;
          background: rgb(255,27,0);
        background: linear-gradient(0deg, rgba(255,27,0,1) 0%, rgba(251,75,2,1) 100%);
        }
        .btn-5:hover {
          color: #f0094a;
          background: transparent;
           box-shadow:none;
        }
        .btn-5:before,
        .btn-5:after{
          content:'';
          position:absolute;
          top:0;
          right:0;
          height:2px;
          width:0;
          background: #f0094a;
          box-shadow:
           -1px -1px 5px 0px #fff,
           7px 7px 20px 0px #0003,
           4px 4px 5px 0px #0002;
          transition:400ms ease all;
        }
        .btn-5:after{
          right:inherit;
          top:inherit;
          left:0;
          bottom:0;
        }
        .btn-5:hover:before,
        .btn-5:hover:after{
          width:100%;
          transition:800ms ease all;
        }
        
        
        /* 6 */
        .btn-6 {
          background: rgb(247,150,192);
        background: radial-gradient(circle, rgba(247,150,192,1) 0%, rgba(118,174,241,1) 100%);
          line-height: 42px;
          padding: 0;
          border: none;
        }
        .btn-6 span {
          position: relative;
          display: block;
          width: 100%;
          height: 100%;
        }
        .btn-6:before,
        .btn-6:after {
          position: absolute;
          content: "";
          height: 0%;
          width: 1px;
         box-shadow:
           -1px -1px 20px 0px rgba(255,255,255,1),
           -4px -4px 5px 0px rgba(255,255,255,1),
           7px 7px 20px 0px rgba(0,0,0,.4),
           4px 4px 5px 0px rgba(0,0,0,.3);
        }
        .btn-6:before {
          right: 0;
          top: 0;
          transition: all 500ms ease;
        }
        .btn-6:after {
          left: 0;
          bottom: 0;
          transition: all 500ms ease;
        }
        .btn-6:hover{
          background: transparent;
          color: #76aef1;
          box-shadow: none;
        }
        .btn-6:hover:before {
          transition: all 500ms ease;
          height: 100%;
        }
        .btn-6:hover:after {
          transition: all 500ms ease;
          height: 100%;
        }
        .btn-6 span:before,
        .btn-6 span:after {
          position: absolute;
          content: "";
          box-shadow:
           -1px -1px 20px 0px rgba(255,255,255,1),
           -4px -4px 5px 0px rgba(255,255,255,1),
           7px 7px 20px 0px rgba(0,0,0,.4),
           4px 4px 5px 0px rgba(0,0,0,.3);
        }
        .btn-6 span:before {
          left: 0;
          top: 0;
          width: 0%;
          height: .5px;
          transition: all 500ms ease;
        }
        .btn-6 span:after {
          right: 0;
          bottom: 0;
          width: 0%;
          height: .5px;
          transition: all 500ms ease;
        }
        .btn-6 span:hover:before {
          width: 100%;
        }
        .btn-6 span:hover:after {
          width: 100%;
        }
        
        /* 7 */
        .btn-7 {
        background: linear-gradient(0deg, rgba(255,151,0,1) 0%, rgba(251,75,2,1) 100%);
          line-height: 42px;
          padding: 0;
          border: none;
        }
        .btn-7 span {
          position: relative;
          display: block;
          width: 100%;
          height: 100%;
        }
        .btn-7:before,
        .btn-7:after {
          position: absolute;
          content: "";
          right: 0;
          bottom: 0;
          background: rgba(251,75,2,1);
          box-shadow:
           -7px -7px 20px 0px rgba(255,255,255,.9),
           -4px -4px 5px 0px rgba(255,255,255,.9),
           7px 7px 20px 0px rgba(0,0,0,.2),
           4px 4px 5px 0px rgba(0,0,0,.3);
          transition: all 0.3s ease;
        }
        .btn-7:before{
           height: 0%;
           width: 2px;
        }
        .btn-7:after {
          width: 0%;
          height: 2px;
        }
        .btn-7:hover{
          color: rgba(251,75,2,1);
          background: transparent;
        }
        .btn-7:hover:before {
          height: 100%;
        }
        .btn-7:hover:after {
          width: 100%;
        }
        .btn-7 span:before,
        .btn-7 span:after {
          position: absolute;
          content: "";
          left: 0;
          top: 0;
          background: rgba(251,75,2,1);
          box-shadow:
           -7px -7px 20px 0px rgba(255,255,255,.9),
           -4px -4px 5px 0px rgba(255,255,255,.9),
           7px 7px 20px 0px rgba(0,0,0,.2),
           4px 4px 5px 0px rgba(0,0,0,.3);
          transition: all 0.3s ease;
        }
        .btn-7 span:before {
          width: 2px;
          height: 0%;
        }
        .btn-7 span:after {
          height: 2px;
          width: 0%;
        }
        .btn-7 span:hover:before {
          height: 100%;
        }
        .btn-7 span:hover:after {
          width: 100%;
        }
        
        /* 8 */
        .btn-8 {
          background-color: #f0ecfc;
        background-image: linear-gradient(315deg, #f0ecfc 0%, #c797eb 74%);
          line-height: 42px;
          padding: 0;
          border: none;
        }
        .btn-8 span {
          position: relative;
          display: block;
          width: 100%;
          height: 100%;
        }
        .btn-8:before,
        .btn-8:after {
          position: absolute;
          content: "";
          right: 0;
          bottom: 0;
          background: #c797eb;
          /*box-shadow:  4px 4px 6px 0 rgba(255,255,255,.5),
                      -4px -4px 6px 0 rgba(116, 125, 136, .2), 
            inset -4px -4px 6px 0 rgba(255,255,255,.5),
            inset 4px 4px 6px 0 rgba(116, 125, 136, .3);*/
          transition: all 0.3s ease;
        }
        .btn-8:before{
           height: 0%;
           width: 2px;
        }
        .btn-8:after {
          width: 0%;
          height: 2px;
        }
        .btn-8:hover:before {
          height: 100%;
        }
        .btn-8:hover:after {
          width: 100%;
        }
        .btn-8:hover{
          background: transparent;
        }
        .btn-8 span:hover{
          color: #c797eb;
        }
        .btn-8 span:before,
        .btn-8 span:after {
          position: absolute;
          content: "";
          left: 0;
          top: 0;
          background: #c797eb;
          /*box-shadow:  4px 4px 6px 0 rgba(255,255,255,.5),
                      -4px -4px 6px 0 rgba(116, 125, 136, .2), 
            inset -4px -4px 6px 0 rgba(255,255,255,.5),
            inset 4px 4px 6px 0 rgba(116, 125, 136, .3);*/
          transition: all 0.3s ease;
        }
        .btn-8 span:before {
          width: 2px;
          height: 0%;
        }
        .btn-8 span:after {
          height: 2px;
          width: 0%;
        }
        .btn-8 span:hover:before {
          height: 100%;
        }
        .btn-8 span:hover:after {
          width: 100%;
        }
          
        
        /* 9 */
        .btn-9 {
          border: none;
          transition: all 0.3s ease;
          overflow: hidden;
        }
        .btn-9:after {
          position: absolute;
          content: " ";
          z-index: -1;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
           background-color: #1fd1f9;
        background-image: linear-gradient(315deg, #1fd1f9 0%, #b621fe 74%);
          transition: all 0.3s ease;
        }
        .btn-9:hover {
          background: transparent;
          box-shadow:  4px 4px 6px 0 rgba(255,255,255,.5),
                      -4px -4px 6px 0 rgba(116, 125, 136, .2), 
            inset -4px -4px 6px 0 rgba(255,255,255,.5),
            inset 4px 4px 6px 0 rgba(116, 125, 136, .3);
          color: #fff;
        }
        .btn-9:hover:after {
          -webkit-transform: scale(2) rotate(180deg);
          transform: scale(2) rotate(180deg);
          box-shadow:  4px 4px 6px 0 rgba(255,255,255,.5),
                      -4px -4px 6px 0 rgba(116, 125, 136, .2), 
            inset -4px -4px 6px 0 rgba(255,255,255,.5),
            inset 4px 4px 6px 0 rgba(116, 125, 136, .3);
        }
        
        /* 10 */
        .btn-10 {
          background: rgb(22,9,240);
        background: linear-gradient(0deg, rgba(22,9,240,1) 0%, rgba(49,110,244,1) 100%);
          color: #fff;
          border: none;
          transition: all 0.3s ease;
          overflow: hidden;
        }
        .btn-10:after {
          position: absolute;
          content: " ";
          top: 0;
          left: 0;
          z-index: -1;
          width: 100%;
          height: 100%;
          transition: all 0.3s ease;
          -webkit-transform: scale(.1);
          transform: scale(.1);
        }
        .btn-10:hover {
          color: #fff;
          border: none;
          background: transparent;
        }
        .btn-10:hover:after {
          background: rgb(0,3,255);
        background: linear-gradient(0deg, rgba(2,126,251,1) 0%,  rgba(0,3,255,1)100%);
          -webkit-transform: scale(1);
          transform: scale(1);
        }
        
        /* 11 */
        .btn-11 {
          border: none;
          background: rgb(251,33,117);
            background: linear-gradient(0deg, rgba(251,33,117,1) 0%, rgba(234,76,137,1) 100%);
            color: #fff;
            overflow: hidden;
        }
        .btn-11:hover {
            text-decoration: none;
            color: #fff;
        }
        .btn-11:before {
            position: absolute;
            content: '';
            display: inline-block;
            top: -180px;
            left: 0;
            width: 30px;
            height: 100%;
            background-color: #fff;
            animation: shiny-btn1 3s ease-in-out infinite;
        }
        .btn-11:hover{
          opacity: .7;
        }
        .btn-11:active{
          box-shadow:  4px 4px 6px 0 rgba(255,255,255,.3),
                      -4px -4px 6px 0 rgba(116, 125, 136, .2), 
            inset -4px -4px 6px 0 rgba(255,255,255,.2),
            inset 4px 4px 6px 0 rgba(0, 0, 0, .2);
        }
        
        
        @-webkit-keyframes shiny-btn1 {
            0% { -webkit-transform: scale(0) rotate(45deg); opacity: 0; }
            80% { -webkit-transform: scale(0) rotate(45deg); opacity: 0.5; }
            81% { -webkit-transform: scale(4) rotate(45deg); opacity: 1; }
            100% { -webkit-transform: scale(50) rotate(45deg); opacity: 0; }
        }
        
        
        /* 12 */
        .btn-12{
          position: relative;
          right: 20px;
          bottom: 20px;
          border:none;
          box-shadow: none;
          width: 130px;
          height: 40px;
          line-height: 42px;
          -webkit-perspective: 230px;
          perspective: 230px;
        }
        .btn-12 span {
          background: rgb(0,172,238);
        background: linear-gradient(0deg, rgba(0,172,238,1) 0%, rgba(2,126,251,1) 100%);
          display: block;
          position: absolute;
          width: 130px;
          height: 40px;
          box-shadow:inset 2px 2px 2px 0px rgba(255,255,255,.5),
           7px 7px 20px 0px rgba(0,0,0,.1),
           4px 4px 5px 0px rgba(0,0,0,.1);
          border-radius: 5px;
          margin:0;
          text-align: center;
          -webkit-box-sizing: border-box;
          -moz-box-sizing: border-box;
          box-sizing: border-box;
          -webkit-transition: all .3s;
          transition: all .3s;
        }
        .btn-12 span:nth-child(1) {
          box-shadow:
           -7px -7px 20px 0px #fff9,
           -4px -4px 5px 0px #fff9,
           7px 7px 20px 0px #0002,
           4px 4px 5px 0px #0001;
          -webkit-transform: rotateX(90deg);
          -moz-transform: rotateX(90deg);
          transform: rotateX(90deg);
          -webkit-transform-origin: 50% 50% -20px;
          -moz-transform-origin: 50% 50% -20px;
          transform-origin: 50% 50% -20px;
        }
        .btn-12 span:nth-child(2) {
          -webkit-transform: rotateX(0deg);
          -moz-transform: rotateX(0deg);
          transform: rotateX(0deg);
          -webkit-transform-origin: 50% 50% -20px;
          -moz-transform-origin: 50% 50% -20px;
          transform-origin: 50% 50% -20px;
        }
        .btn-12:hover span:nth-child(1) {
          box-shadow:inset 2px 2px 2px 0px rgba(255,255,255,.5),
           7px 7px 20px 0px rgba(0,0,0,.1),
           4px 4px 5px 0px rgba(0,0,0,.1);
          -webkit-transform: rotateX(0deg);
          -moz-transform: rotateX(0deg);
          transform: rotateX(0deg);
        }
        .btn-12:hover span:nth-child(2) {
          box-shadow:inset 2px 2px 2px 0px rgba(255,255,255,.5),
           7px 7px 20px 0px rgba(0,0,0,.1),
           4px 4px 5px 0px rgba(0,0,0,.1);
         color: transparent;
          -webkit-transform: rotateX(-90deg);
          -moz-transform: rotateX(-90deg);
          transform: rotateX(-90deg);
        }
        
        
        /* 13 */
        .btn-13 {
          background-color: #89d8d3;
        background-image: linear-gradient(315deg, #89d8d3 0%, #03c8a8 74%);
          border: none;
          z-index: 1;
        }
        .btn-13:after {
          position: absolute;
          content: "";
          width: 100%;
          height: 0;
          bottom: 0;
          left: 0;
          z-index: -1;
          border-radius: 5px;
           background-color: #4dccc6;
        background-image: linear-gradient(315deg, #4dccc6 0%, #96e4df 74%);
          box-shadow:
           -7px -7px 20px 0px #fff9,
           -4px -4px 5px 0px #fff9,
           7px 7px 20px 0px #0002,
           4px 4px 5px 0px #0001;
          transition: all 0.3s ease;
        }
        .btn-13:hover {
          color: #fff;
        }
        .btn-13:hover:after {
          top: 0;
          height: 100%;
        }
        .btn-13:active {
          top: 2px;
        }
        
        
        /* 14 */
        .btn-14 {
          background: rgb(255,151,0);
          border: none;
          z-index: 1;
        }
        .btn-14:after {
          position: absolute;
          content: "";
          width: 100%;
          height: 0;
          top: 0;
          left: 0;
          z-index: -1;
          border-radius: 5px;
          background-color: #eaf818;
          background-image: linear-gradient(315deg, #eaf818 0%, #f6fc9c 74%);
           box-shadow:inset 2px 2px 2px 0px rgba(255,255,255,.5);
           7px 7px 20px 0px rgba(0,0,0,.1),
           4px 4px 5px 0px rgba(0,0,0,.1);
          transition: all 0.3s ease;
        }
        .btn-14:hover {
          color: #000;
        }
        .btn-14:hover:after {
          top: auto;
          bottom: 0;
          height: 100%;
        }
        .btn-14:active {
          top: 2px;
        }
        
        /* 15 */
        .btn-15 {
          background: #b621fe;
          border: none;
          z-index: 1;
        }
        .btn-15:after {
          position: absolute;
          content: "";
          width: 0;
          height: 100%;
          top: 0;
          right: 0;
          z-index: -1;
          background-color: #663dff;
          border-radius: 5px;
           box-shadow:inset 2px 2px 2px 0px rgba(255,255,255,.5),
           7px 7px 20px 0px rgba(0,0,0,.1),
           4px 4px 5px 0px rgba(0,0,0,.1);
          transition: all 0.3s ease;
        }
        .btn-15:hover {
          color: #fff;
        }
        .btn-15:hover:after {
          left: 0;
          width: 100%;
        }
        .btn-15:active {
          top: 2px;
        }
        
        
        /* 16 */
        .btn-16 {
          border: none;
          color: #000;
        }
        .btn-16:after {
          position: absolute;
          content: "";
          width: 0;
          height: 100%;
          top: 0;
          left: 0;
          direction: rtl;
          z-index: -1;
          box-shadow:
           -7px -7px 20px 0px #fff9,
           -4px -4px 5px 0px #fff9,
           7px 7px 20px 0px #0002,
           4px 4px 5px 0px #0001;
          transition: all 0.3s ease;
        }
        .btn-16:hover {
          color: #000;
        }
        .btn-16:hover:after {
          left: auto;
          right: 0;
          width: 100%;
        }
        .btn-16:active {
          top: 2px;
        }
    </style>
</head>

<?php if ($tag === ''){?>

<style>
    @import url('https://fonts.googleapis.com/css?family=Audiowide&display=swap');
    
    html,
    body{
      margin: 0px;
      overflow: hidden;
    }
    
    div{
      position: absolute;
      top: 0%;
      left: 0%;
      height: 100%;
      width: 100%;
      margin: 0px;
      background: radial-gradient(circle, #240015 0%, #12000b 100%);
      overflow: hidden;
    }
    
    .wrap{
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    }
    
    h2{
      position: absolute;
      top: 50%;
      left: 50%;
      margin-top: 150px;
      font-size: 32px;
      text-transform: uppercase;
      transform: translate(-50%, -50%);
      display: block;
      color: #12000a;
      font-weight: 300;
      font-family: Audiowide;
      text-shadow: 0px 0px 4px #12000a;
      animation: fadeInText 3s ease-in 3.5s forwards, flicker4 5s linear 7.5s infinite, hueRotate 6s ease-in-out 3s infinite;
    }
    
    #svgWrap_1,
    #svgWrap_2{
      position: absolute;
      height: auto;
      width: 600px;
      max-width: 100%;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
    
    #svgWrap_1,
    #svgWrap_2,
    div{
      animation: hueRotate 6s ease-in-out 3s infinite;
    }
    
    #id1_1,
    #id2_1,
    #id3_1{
      stroke: #ff005d;
      stroke-width: 3px;
      fill: transparent;
      filter: url(#glow);
    }
    
    #id1_2,
    #id2_2,
    #id3_2{
      stroke: #12000a;
      stroke-width: 3px;
      fill: transparent;
      filter: url(#glow);
    }
    
    #id3_1{
      stroke-dasharray: 940px;
      stroke-dashoffset: -940px;
      animation: drawLine3 2.5s ease-in-out 0s forwards, flicker3 4s linear 4s infinite;
    }
    
    #id2_1{
      stroke-dasharray: 735px;
      stroke-dashoffset: -735px;
      animation: drawLine2 2.5s ease-in-out 0.5s forwards, flicker2 4s linear 4.5s infinite;
    }
    
    #id1_1{
      stroke-dasharray: 940px;
      stroke-dashoffset: -940px;
      animation: drawLine1 2.5s ease-in-out 1s forwards, flicker1 4s linear 5s infinite;
    }
    
    @keyframes drawLine1 {
      0%  {stroke-dashoffset: -940px;}
      100%{stroke-dashoffset: 0px;}
    }
    
    @keyframes drawLine2 {
      0%  {stroke-dashoffset: -735px;}
      100%{stroke-dashoffset: 0px;}
    }
    
    @keyframes drawLine3 {
      0%  {stroke-dashoffset: -940px;}
      100%{stroke-dashoffset: 0px;}
    }
    
    @keyframes flicker1{
      0%  {stroke: #ff005d;}
      1%  {stroke: transparent;}
      3%  {stroke: transparent;}
      4%  {stroke: #ff005d;}
      6%  {stroke: #ff005d;}
      7%  {stroke: transparent;}
      13% {stroke: transparent;}
      14% {stroke: #ff005d;}
      100%{stroke: #ff005d;}
    }
    
    @keyframes flicker2{
      0%  {stroke: #ff005d;}
      50% {stroke: #ff005d;}
      51% {stroke: transparent;}
      61% {stroke: transparent;}
      62% {stroke: #ff005d;}
      100%{stroke: #ff005d;}
    }
    
    @keyframes flicker3{
      0%  {stroke: #ff005d;}
      1%  {stroke: transparent;}
      10% {stroke: transparent;}
      11% {stroke: #ff005d;}
      40% {stroke: #ff005d;}
      41% {stroke: transparent;}
      45% {stroke: transparent;}
      46% {stroke: #ff005d;}
      100%{stroke: #ff005d;}
    }
    
    @keyframes flicker4{
      0%  {color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
      30% {color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
      31% {color: #12000a;text-shadow:0px 0px 4px #12000a;}
      32% {color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
      36% {color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
      37% {color: #12000a;text-shadow:0px 0px 4px #12000a;}
      41% {color: #12000a;text-shadow:0px 0px 4px #12000a;}
      42% {color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
      85% {color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
      86% {color: #12000a;text-shadow:0px 0px 4px #12000a;}
      95% {color: #12000a;text-shadow:0px 0px 4px #12000a;}
      96% {color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
      100%{color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
    }
    
    @keyframes fadeInText{
      1%  {color: #12000a;text-shadow:0px 0px 4px #12000a;}
      70% {color: #ff005d;text-shadow:0px 0px 14px #ff005d;}
      100%{color: #ff005d;text-shadow:0px 0px 4px #ff005d;}
    }
    
    @keyframes hueRotate{
      0%  {
        filter: hue-rotate(0deg);
      }
      50%  {
        filter: hue-rotate(-120deg);
      }
      100%  {
        filter: hue-rotate(0deg);
      }
    }
</style>


<div></div>
<svg id="svgWrap_2" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 700 250">
  <g>
    <path id="id3_2" d="M195.7 232.67h-37.1V149.7H27.76c-2.64 0-5.1-.5-7.36-1.49-2.27-.99-4.23-2.31-5.88-3.96-1.65-1.65-2.95-3.61-3.89-5.88s-1.42-4.67-1.42-7.22V29.62h36.82v82.98H158.6V29.62h37.1v203.05z"/>
    <path id="id2_2" d="M470.69 147.71c0 8.31-1.06 16.17-3.19 23.58-2.12 7.41-5.12 14.28-8.99 20.6-3.87 6.33-8.45 11.99-13.74 16.99-5.29 5-11.07 9.28-17.35 12.81a85.146 85.146 0 0 1-20.04 8.14 83.637 83.637 0 0 1-21.67 2.83H319.3c-7.46 0-14.73-.94-21.81-2.83-7.08-1.89-13.76-4.6-20.04-8.14a88.292 88.292 0 0 1-17.35-12.81c-5.29-5-9.84-10.67-13.66-16.99-3.82-6.32-6.8-13.19-8.92-20.6-2.12-7.41-3.19-15.27-3.19-23.58v-33.13c0-12.46 2.34-23.88 7.01-34.27 4.67-10.38 10.92-19.33 18.76-26.83 7.83-7.5 16.87-13.36 27.12-17.56 10.24-4.2 20.93-6.3 32.07-6.3h66.41c7.36 0 14.58.94 21.67 2.83 7.08 1.89 13.76 4.6 20.04 8.14a88.292 88.292 0 0 1 17.35 12.81c5.29 5 9.86 10.67 13.74 16.99 3.87 6.33 6.87 13.19 8.99 20.6 2.13 7.41 3.19 15.27 3.19 23.58v33.14zm-37.1-33.13c0-7.27-1.32-13.88-3.96-19.82-2.64-5.95-6.16-11.04-10.55-15.29-4.39-4.25-9.46-7.5-15.22-9.77-5.76-2.27-11.8-3.35-18.13-3.26h-66.41c-6.14-.09-12.11.97-17.91 3.19-5.81 2.22-10.95 5.43-15.44 9.63-4.48 4.2-8.07 9.3-10.76 15.29-2.69 6-4.04 12.67-4.04 20.04v33.13c0 7.36 1.32 14.02 3.96 19.97 2.64 5.95 6.18 11.02 10.62 15.22 4.44 4.2 9.56 7.43 15.36 9.7 5.8 2.27 11.87 3.35 18.2 3.26h66.41c7.27 0 13.85-1.2 19.75-3.61s10.93-5.73 15.08-9.98 7.36-9.32 9.63-15.22c2.27-5.9 3.4-12.34 3.4-19.33v-33.15zm-16-26.91a17.89 17.89 0 0 1 2.83 6.73c.47 2.41.47 4.77 0 7.08-.47 2.31-1.39 4.48-2.76 6.51-1.37 2.03-3.14 3.75-5.31 5.17l-99.4 66.41c-1.61 1.23-3.26 2.08-4.96 2.55-1.7.47-3.45.71-5.24.71-3.02 0-5.9-.71-8.64-2.12-2.74-1.42-4.96-3.44-6.66-6.09a17.89 17.89 0 0 1-2.83-6.73c-.47-2.41-.5-4.77-.07-7.08.43-2.31 1.3-4.48 2.62-6.51 1.32-2.03 3.07-3.75 5.24-5.17l99.69-66.41a17.89 17.89 0 0 1 6.73-2.83c2.41-.47 4.77-.47 7.08 0 2.31.47 4.48 1.37 6.51 2.69 2.03 1.32 3.75 3.02 5.17 5.09z"/>
    <path id="id1_2" d="M688.33 232.67h-37.1V149.7H520.39c-2.64 0-5.1-.5-7.36-1.49-2.27-.99-4.23-2.31-5.88-3.96-1.65-1.65-2.95-3.61-3.89-5.88s-1.42-4.67-1.42-7.22V29.62h36.82v82.98h112.57V29.62h37.1v203.05z"/>
  </g>
</svg>
<svg id="svgWrap_1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 700 250">
  <g>
    <path id="id3_1" d="M195.7 232.67h-37.1V149.7H27.76c-2.64 0-5.1-.5-7.36-1.49-2.27-.99-4.23-2.31-5.88-3.96-1.65-1.65-2.95-3.61-3.89-5.88s-1.42-4.67-1.42-7.22V29.62h36.82v82.98H158.6V29.62h37.1v203.05z"/>
    <path id="id2_1" d="M470.69 147.71c0 8.31-1.06 16.17-3.19 23.58-2.12 7.41-5.12 14.28-8.99 20.6-3.87 6.33-8.45 11.99-13.74 16.99-5.29 5-11.07 9.28-17.35 12.81a85.146 85.146 0 0 1-20.04 8.14 83.637 83.637 0 0 1-21.67 2.83H319.3c-7.46 0-14.73-.94-21.81-2.83-7.08-1.89-13.76-4.6-20.04-8.14a88.292 88.292 0 0 1-17.35-12.81c-5.29-5-9.84-10.67-13.66-16.99-3.82-6.32-6.8-13.19-8.92-20.6-2.12-7.41-3.19-15.27-3.19-23.58v-33.13c0-12.46 2.34-23.88 7.01-34.27 4.67-10.38 10.92-19.33 18.76-26.83 7.83-7.5 16.87-13.36 27.12-17.56 10.24-4.2 20.93-6.3 32.07-6.3h66.41c7.36 0 14.58.94 21.67 2.83 7.08 1.89 13.76 4.6 20.04 8.14a88.292 88.292 0 0 1 17.35 12.81c5.29 5 9.86 10.67 13.74 16.99 3.87 6.33 6.87 13.19 8.99 20.6 2.13 7.41 3.19 15.27 3.19 23.58v33.14zm-37.1-33.13c0-7.27-1.32-13.88-3.96-19.82-2.64-5.95-6.16-11.04-10.55-15.29-4.39-4.25-9.46-7.5-15.22-9.77-5.76-2.27-11.8-3.35-18.13-3.26h-66.41c-6.14-.09-12.11.97-17.91 3.19-5.81 2.22-10.95 5.43-15.44 9.63-4.48 4.2-8.07 9.3-10.76 15.29-2.69 6-4.04 12.67-4.04 20.04v33.13c0 7.36 1.32 14.02 3.96 19.97 2.64 5.95 6.18 11.02 10.62 15.22 4.44 4.2 9.56 7.43 15.36 9.7 5.8 2.27 11.87 3.35 18.2 3.26h66.41c7.27 0 13.85-1.2 19.75-3.61s10.93-5.73 15.08-9.98 7.36-9.32 9.63-15.22c2.27-5.9 3.4-12.34 3.4-19.33v-33.15zm-16-26.91a17.89 17.89 0 0 1 2.83 6.73c.47 2.41.47 4.77 0 7.08-.47 2.31-1.39 4.48-2.76 6.51-1.37 2.03-3.14 3.75-5.31 5.17l-99.4 66.41c-1.61 1.23-3.26 2.08-4.96 2.55-1.7.47-3.45.71-5.24.71-3.02 0-5.9-.71-8.64-2.12-2.74-1.42-4.96-3.44-6.66-6.09a17.89 17.89 0 0 1-2.83-6.73c-.47-2.41-.5-4.77-.07-7.08.43-2.31 1.3-4.48 2.62-6.51 1.32-2.03 3.07-3.75 5.24-5.17l99.69-66.41a17.89 17.89 0 0 1 6.73-2.83c2.41-.47 4.77-.47 7.08 0 2.31.47 4.48 1.37 6.51 2.69 2.03 1.32 3.75 3.02 5.17 5.09z"/>
    <path id="id1_1" d="M688.33 232.67h-37.1V149.7H520.39c-2.64 0-5.1-.5-7.36-1.49-2.27-.99-4.23-2.31-5.88-3.96-1.65-1.65-2.95-3.61-3.89-5.88s-1.42-4.67-1.42-7.22V29.62h36.82v82.98h112.57V29.62h37.1v203.05z"/>
  </g>
</svg>

<svg>
  <defs>
    <filter id="glow">
      <fegaussianblur class="blur" result="coloredBlur" stddeviation="4"></fegaussianblur>
      <femerge>
        <femergenode in="coloredBlur"></femergenode>
        <femergenode in="SourceGraphic"></femergenode>
      </femerge>
    </filter>
  </defs>
</svg>

<h2>Your App ID cannot be found.</h2>


<?php }else{?>
<body>
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fa fa-moon"></i>
        <i class="fa fa-sun"></i>
    </button>
    <div class="container">
        <img src="https://i.ibb.co/BHkrkNvv/promos-dark.png" alt="Light Logo" class="logo light">
        <img src="https://i.ibb.co/F4rN0KXY/promos.png" alt="Dark Logo" class="logo dark">
        <h1>Add Playlist إضافة قائمة تشغيل</h1>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="submit" class="custom-btn btn-11">Submit</button>
        </form>
    </div>
    <div class="copyright">
        &copy; <?php echo date("Y"); ?> All Rights Reserved by @USEDR On Telegram 🩵
    </div>
    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
        }
    </script>
</body>
<?php }?>
</html>