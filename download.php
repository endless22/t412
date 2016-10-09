<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;
$state = $t411->getCredentials();
$t411->id = isset($_GET['id']) && ctype_digit($_GET['id']) ? $_GET['id'] : $t411->home();
$t411->getDetails();
$nom = strtr($t411->details->name, '.', ' ');
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>T412 | <?php echo $t411->details->name;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="/">Torrent</a></li>
      <li><a><?php echo $t411->details->categoryname; ?></a></li>
      <li><a href="/details/<?php echo $t411->id; ?>/"><?php echo $nom; ?></a></li>
    </ol>

<?php if(!empty($state->user)) { $t411->addTorrent($t411->id);?>

    <div class="jumbotron">
      <h1 style="color:green">Torrent ajouté</h1>
      <p>Le torrent <i><?php echo $nom; ?></i> a bien été téléchargé.</p>
    </div>

<?php } else { ?>

    <div class="jumbotron">
      <h1 style="color:red">Aucune seedbox trouvée</h1>
      <p>Seul le <a href="/dl/<?php echo $t411->id;?>">téléchargement direct</a> est possible pour vous.</p>
    </div>
<?php } ?>
  </div>
  <script src="/js/jquery.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
  </body>
</html>
