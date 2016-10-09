<?php
require_once __DIR__ . '/' . 'syno.class.php';

echo 'WIP';
exit;

$hash = !empty($_GET['hash']) && ctype_alnum($_GET['hash']) ? $_GET['hash'] : null;
$link = empty($hash) && !empty($_GET['link']) ? $_GET['link'] : null;
empty($hash) && empty($link) ? $t411->home() : null;

$syno = new Syno($hash, $link);
$syno->user = 'blah';
$syno->pass = 'blah';
$syno->protocol = 'http';
$syno->ip = 'blahblah';
$syno->port = '5000';
$syno->buildTask();
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>T412 | <?php echo isset($syno->reponse->name) ? $syno->reponse->name : 'Téléchargement sur NAS Synology';?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="/">Téléchargement</a></li>
      <li><?php echo isset($syno->reponse->name) ? $syno->reponse->name : null; ?></li>
    </ol>

<?php if (empty($syno->error)) { ?>

    <div class="jumbotron">
      <h1 style="color:green">Torrent ajouté</h1>
      <p>Le téléchargement de <i><?php echo $syno->name; ?></i> a bien été envoyé sur le NAS <?php echo $syno->ip; ?> (/<?php echo isset($syno->destination) ? $syno->destination : 'inconnu'; ?>).</p>
    </div>

<?php } else { ?>

    <div class="jumbotron">
      <h1 style="color:red">Erreur !</h1>
      <p>Le téléchargement de <i><?php echo isset($syno->name) ? $syno->name : '(lien inconnu)'; ?></i> sur le NAS <?php echo $syno->ip; ?> (/<?php echo isset($syno->destination) ? $syno->destination : 'inconnu'; ?>) a échoué (<?php echo $syno->error; ?>).</p>
    </div>

<?php } ?>

  </div>
  <script src="/js/jquery.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
  </body>
</html>
