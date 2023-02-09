<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    <p>
        Welcome To Pax Alto 
    </p>
    
    <pre><?= json_encode($GLOBALS['_'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></pre>

</body>
</html>