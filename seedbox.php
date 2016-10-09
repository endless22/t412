<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;

$header = 'Aucune seedbox n\'est configurée.';
$state = $t411->getCredentials();

$username = !empty($state->user) ? $t411->dechiffrer($state->user) : null;
$adresse = !empty($state->host) ? $t411->dechiffrer($state->host) : null;
$portserv = !empty($state->port) ? $state->port : null;
if (!empty($state->user)) {
  $header = 'Actuellement, une seedbox est configurée (<span style="color:green">' . $username . '</span>@<span style="color:green">' .$adresse . '</span>:<span style="color:green">' . $portserv . '</span>).';
}

if(isset($_POST['user']) && !empty($_POST['user'])) {
  if($t411->tryConnection($_POST['address'], $_POST['port'], $_POST['user'], $_POST['password'])) {
    $t411->storeSeedbox($t411->chiffrer($_POST['address']), $_POST['port'], $t411->chiffrer($_POST['user']), $t411->chiffrer($_POST['password']), $t411->chiffrer($_POST['email']));
    header('Refresh: 0');
  } else { $header = '<span style="color:red">Echec lors de l\'ajout.</span>'; }
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>t412 | Seedbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="/">Torrents</a></li>
      <li><a href="/seedbox">Configuration seedbox</a></li>
    </ol>

    <div class="jumbotron">
      <h1 style="color:green">Mes identifiants</h1>
      <p class="lo">Le téléchargement sur seedbox et le <a href="/suivi/">suivi de séries</a> nécessite de connaitre vos identifiants <b>transmission</b> et <b>votre adresse mail</b>. Le service utilise un chiffrement fort <b>AES-256</b> pour protéger vos données, personne n'aura l'occasion de les voir.</p>
      <p><?php echo $header; ?></p>
    </div>

    <div>
      <h1><small>Ajouter mes identifiants</small></h1>
    </div>

    <form class="forme" role="form" action="/seedbox.php" method="post">
      <h3>Transmission</h3>
      <div class="col-lg-3">
        <label for="address">Adresse seedbox</label>
        <input type="text" class="form-control" id="address" name="address" placeholder="example.com" required>
      </div>
      <div class="col-lg-3">
        <label for="port">Port</label>
        <input type="text" class="form-control" id="port" name="port" value="9091" required>
      </div>
      <div class="col-lg-3">
        <label for="user">Nom d'utilisateur</label>
        <input type="text" class="form-control" id="user" name="user" placeholder="toto" required>
      </div>
      <div class="col-lg-3">
        <label for="password">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <h3 class="seedbox">T411</h3>
      <div class="col-lg-3">
        <label for="email">Adresse email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="titi@example.com" required>
      </div>
      <div class="col-xs-6 col-md-3 col-lg-3">
        <label for="add">Et voilà !</label>
        <button class="btn btn-primary btn-block" id="add" type="submit"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> Enregistrer</button>
      </div>

    </form>
  </div>
  <script src="/js/jquery.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
  </body>
</html>
