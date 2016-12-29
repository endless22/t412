<?php
require_once 'utils.class.php';
$ttl = isset($_POST['remember']) ? time()+7776000 : 0;

if (!empty($_POST['username']) && !empty($_POST['password'])) {
  $t411 = new Utils(false);
  $t411->Auth($_POST['username'], $_POST['password'], $ttl);
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>t412 - Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">

    <link rel="stylesheet" href="css/bootstrap.min.css" id="bootstrap-css">
    <link rel="stylesheet" href="css/login.css">
  </head>
  <body>

  <div class="container">
    <div class="row vertical-align">
      <div class="col-xs-12 col-sm-6 col-md-4 col-sm-offset-3 col-md-offset-4">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><?php echo empty($t411->reponse->error) ? 'Connecte toi, bitch.' : '<span style="color:red">'.$t411->reponse->error.'</span>'; ?></h3>
          </div>
          <div class="panel-body">
            <form action="login.php" class="validate-form" method="post">
              <div class="form-group">
                <input class="form-control" placeholder="Nom d'utilisateur T411" name="username" type="text" required="true">
              </div>
              <div class="form-group">
                <input class="form-control" placeholder="Mot de passe" name="password" type="password" value="" required="true">
              </div>
              <div class="checkbox">
                <label><input name="remember" type="checkbox" value="Remember Me" checked> Rester connecté</label>
              </div>
              <input class="btn btn-lg btn-primary btn-block" id="btn" name="submit" type="submit" value="Connexion">
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="js/jquery.min.js"></script>
  <script src="js/validate.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/TweenLite.min.js"></script>
  <script src="js/parallax.js"></script>
</body>
</html>