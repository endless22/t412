<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;
$t411->order = 'name';
$t411->sort_order = 4;
$state = $t411->getCredentials();
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>t412 | Mes téléchargements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/list.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="/">Torrents</a></li>
      <li><a href="/downloads/">Mes téléchargements</a></li>
    </ol>

    <ul class="list-group">

<?php if(empty($state->user)) { ?>

    <div class="page-header">
      <h1><small>Pour suivre vos téléchargements, veuillez <a href="/seedbox/">configurer une seedbox.</a></small></h1>
    </div>

<?php } else {

$reponse = $t411->listTorrents();
foreach ($reponse as $key => $torrent) {
  $status = $torrent->getStatus() == 6 ? 'success' : 'warning';
?>
     <li class="list-group-item list-group-item-<?php echo $status;?> title" data-toggle="collapse" data-target="#<?php echo $key;?>">
        <?php echo $torrent->getName(); ?>
        <?php echo '<span class="badge progress-bar-' . $status . '">' . ($status == 'warning' ? $torrent->getPercentDone()*100 . '%' : count($torrent->getFiles())) . '</span>'; ?>
        <ul class="nav nav-list collapse" id="<?php echo $key;?>">
          <span class="label label-success"><?php echo $t411->formatBytes($torrent->getSize()); ?> reçu</span>
          <span class="label label-danger"><?php echo $t411->formatBytes($torrent->getUploadedEver());?> envoyé</span>
          <span class="label label-default"><?php echo '(Ratio ' . sprintf('%0.2f', $torrent->getUploadedEver()/$torrent->getSize()); ?>)</span><br>
<?php if($t411->uid == '666666666') { // désactive le DL Syno pour l'instant ?>
          <ul>
            <li><a href="/synology.php?hash=<?php echo $torrent->getHash(); ?>"><span class="glyphicon glyphicon-cloud-download"></span> Envoyer sur syno.</a></li>
<?php foreach ($torrent->getFiles() as $file) { ?>
            <li><a href="/synology.php?link=<?php echo urlencode($file->getName());?>"><span class="glyphicon glyphicon-download"></span> <?php echo strtr($file->getName(), [$torrent->getName().'/' => '']);?></a> <span class="size">(<?php echo $t411->formatBytes($file->getSize());?>)</span></li>
<?php } ?>
          </ul>
<?php } ?>
        </ul>
      </li>

<?php
  }
}
?>
    </ul>
  </div>
  <script src="/js/jquery.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
  </body>
</html>
