<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/bootstrap-theme.min.css"/>
    <title>Refresh token</title>
</head>
<body>
    <div class="container">
        <h1>Refresh Access Token</h1>
        <div class="panel panel-primary">
          <div class="panel-heading">Status:</div>
          <div class="panel-body"> <?php echo $status ?>
            <pre><?php echo $tokenValue ?></pre>
          </div>
        </div>
    <a href="index.php" class="btn btn-primary">Back</a> <a href="refreshToken.php" class="btn btn-primary">Retry token refresh</a>
    </div>
</body>
</html>