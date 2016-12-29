<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;

$type = isset($_GET['type']) && ctype_alpha($_GET['type']) ? $_GET['type'] : $t411->home();
$idserv = isset($_GET['idserv']) && ctype_digit($_GET['idserv']) ? $_GET['idserv'] : $t411->home();
$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? $_GET['id'] : $t411->home();
$data = isset($_GET['data']) ? $_GET['data'] : null;

$naslist = $t411->getNasServers();
$server = $t411->getseedbox($idserv);
$t411->getFullDetails($id);
if ($type == 'torrent') {  $nom = strtr($t411->details->name, '.', ' '); }
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>T412 - <?php echo isset($t411->details->name) ? $t411->details->name : null;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="index.php">Torrent</a></li>
      <li><a><?php echo isset($t411->details->categoryname) ? $t411->details->categoryname : null; ?></a></li>
      <li><a><?php echo isset($nom) ? $nom : null; ?></a></li>
    </ol>

    <div class="jumbotron">
<?php if (isset($_POST['submit']) && !empty($_POST['dest'])) { $t411->addDownloadQueue($idserv, $_POST['dest'], $t411->hash); ?>

      <h1 style="color:green">Téléchargement planifié</h1>
      <p>Le torrent <i><?php echo isset($nom) ? $nom : null; ?></i> a bien été ajouté à la liste des téléchargements.</p>

<?php } elseif ($type == 'hash' || $type == 'link') try { $t411->addTorrent($type, $id, $idserv, $data); ?>

      <h1 style="color:green">Torrent ajouté</h1>
      <p>Le torrent <i><?php echo isset($nom) ? $nom : null; ?></i> a bien été téléchargé.</p>

<?php } catch (Exception $e) { ?>

      <h1 style="color:red">Erreur !</h1>
      <p>Impossible de télécharger le torrent (raison: <?php echo $e->getMessage(); ?>).</p>

<?php } else try { $t411->addTorrent($type, $id, $idserv); ?>

      <h1 style="color:green">Torrent ajouté</h1>
      <p>Le torrent <i><?php echo isset($nom) ? $nom : null; ?></i> a bien été téléchargé.</p>

<?php } catch (Exception $e) { ?>

      <h1 style="color:red">Erreur !</h1>
      <p>Impossible de télécharger le torrent (raison: <?php echo $e->getMessage(); ?>).</p>

<?php } ?>
    </div>

<?php if ($server->type == 'transmission' && $type == 'torrent' && !empty(Utils::DL_PREFIX) && $_COOKIE['username'] == Utils::T411USER && !empty($naslist)) { ?>
    <form class="form-inline" name="addtoqueue" action="" method="POST">
      <input type="hidden" name="e" value="ehlo">
      <h4>Vous avez la possibilité de planifier le rapatriement du téléchargement via HTTP sur:
      <select name="dest" id="dest" class="form-control" style="width:auto;" onchange="updatePort()">
<?php foreach ($naslist as $value) { ?>
        <option value="<?php echo $value->id; ?>"><?php echo $t411->decrypt($value->name); ?></option>
<?php } ?>
      </select>
      <button class="btn btn-primary" id="add" name="submit" type="submit">Ajouter</button></h4>
    </form>
<?php } ?>

  </div>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  </body>
</html>