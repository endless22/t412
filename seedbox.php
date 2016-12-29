<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;

/**
 * @todo modifier cette merde >_<
 */
if (isset($_POST['submit']) && $_POST['type'] != 'local') {
  $test = $t411->testConnection($_POST['type'], $_POST['address'], $_POST['port'], $_POST['username'], $_POST['password']);
  if ($test === true) {
    $t411->storeSeedbox($t411->encrypt($_POST['name']), $_POST['type'], $t411->encrypt($_POST['address']), $_POST['port'], $t411->encrypt($_POST['username']), $t411->encrypt($_POST['password']), $_POST['folder']);
  }
} elseif (isset($_POST['submit']) && $_POST['type'] == 'local') {
  $test = $t411->testConnection($_POST['type'], null, null, null, null, $_POST['folder']);
  if ($test === true) {
    $t411->storeSeedbox($t411->encrypt($_POST['name']), $_POST['type'], null, null, null, null, $_POST['folder']);
  }
}

if (!empty($_GET['delete'])) {
  $t411->deleteServer($_GET['delete']);
}

$sb = $t411->getSeedboxes();
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>t412 - Seedbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

<?php if (isset($test) && $test === true) { ?>
    <div class="alert alert-success alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      Ajout du serveur <strong><?php echo htmlspecialchars($_POST['type']); ?></strong> (<strong><?php echo ($_POST['type'] != 'local') ? htmlspecialchars($_POST['address']) : $_POST['type']; ?></strong>) réussi.
    </div>
<?php } elseif (isset($test) && $test !== true) { ?>
    <div class="alert alert-warning alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      Impossible de se connecter au serveur <strong><?php echo htmlspecialchars($_POST['type']); ?></strong> à l'adresse <strong><?php ($_POST['type'] != 'local') ? htmlspecialchars($_POST['address']) : $_POST['type']; ?> (<?php echo $test; ?>)</strong>.
    </div>
<?php } ?>

<?php if (!empty($_GET['delete'])) { ?>
    <div class="alert alert-success alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      Serveur supprimé avec succès.
    </div>
<?php } ?>

    <div class="jumbotron">
      <h1 style="color:green">Mes serveurs</h1>
      <p class="lo">Le téléchargement sur seedbox et le <a href="suivi.php">suivi des séries</a> nécessite de connaitre vos identifiants <b>Transmission</b> ou <b>Synology</b>. Le service utilise un chiffrement fort <b>AES-256</b> pour protéger vos données, personne n'aura l'occasion de les voir.</p>
    </div>

    <form class="servers" name="servers" role="form" action="seedbox.php" method="post">
      <div class="col-lg-3">
        <label for="name">Nom</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="ex: seedbox online" required="true">
      </div>
      <div class="col-lg-3">
        <label for="address">Adresse</label>
        <input type="text" class="form-control" id="address" name="address" placeholder="ex: domain.tld ou 127.0.0.1" required="true">
      </div>
      <div class="col-lg-3">
        <label for="type">Type</label>
        <select name="type" id="type" class="form-control" onchange="updatePort()">
          <option selected value="transmission">Transmission</option>
          <option value="synology">Synology</option>
          <option value="local">Local (écriture fichier)</option>
        </select>
      </div>
      <div class="col-lg-3">
        <label for="port">Port</label>
        <input type="text" class="form-control" id="port" name="port" value="9091" required="true">
      </div>
      <div class="col-lg-3">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="ex: toto" autocomplete="off" required="true">
      </div>
      <div class="col-lg-3">
        <label for="password">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" autocomplete="off" required="true">
      </div>
      <div class="col-lg-3">
        <label for="folder">Dossier</label>
        <input type="text" class="form-control" id="folder" name="folder" placeholder="dossier (facultatif)">
      </div>
      <div class="col-lg-3">
        <label for="add">Terminer</label>
        <button class="btn btn-primary btn-block form-control" id="add" type="submit" name="submit"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> Tester et enregistrer</button>
      </div>
    </form>

    <div>
      <h1><small>Mes serveurs</small></h1>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th class="textcentered">Nom</th>
            <th class="textcentered">Type</th>
            <th class="textcentered">Hôte</th>
            <th class="textcentered">Port</th>
            <th class="textcentered">Utilisateur</th>
            <th class="textcentered">Dossier</th>
            <th class="textcentered">Action</th>
          </tr>
        </thead>
        <tbody>

<?php foreach ($sb as $value) { ?>
          <tr>
            <td nowrap class="textcentered"><?php echo $t411->decrypt($value->name);?></td>
            <td nowrap class="textcentered"><?php echo $value->type;?></td>
            <td class="textcentered"><?php echo $t411->decrypt($value->host); ?></td>
            <td class="textcentered"><?php echo $value->port; ?></td>
            <td nowrap class="textcentered"><?php echo $t411->decrypt($value->username);?></td>
            <td nowrap class="textcentered"><?php echo $value->folder;?></td>
            <td class="textcentered"><a href="seedbox.php?delete=<?php echo $value->id;?>"><span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span> Supprimer</a></td>
          </tr>
<?php } ?>
        </tbody>
      </table>
    </div>

  </div>
  <script>
    function updatePort() {
      var e = document.getElementById("type").value;
      document.getElementById("port").value = (e == "transmission") ? "9091" : "5001";
      document.getElementById("folder").required = (e == "local") ? true : false;
      document.getElementById("folder").placeholder = (e == "local") ? "dossier (obligatoire)" : "dossier (facultatif)";

      var champs = ["address", "port", "username", "password"];
      champs.forEach(function(input) {
        document.getElementById(input).disabled = (e == "local") ? true : false;
        document.getElementById(input).required = (e == "local") ? false : true;
      });
    }
  </script>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  </body>
</html>
