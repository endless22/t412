<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;
$transmissions = $t411->getTransmissionServers();
$naslist = $t411->getNasServers();
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>t412 - Mes téléchargements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/list.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="../index.php">Torrents</a></li>
      <li><a href="../downloads.php">Mes téléchargements</a></li>
    </ol>

<?php if (empty($transmissions)) { ?>

    <div class="page-header">
      <h1><small>Pour suivre vos téléchargements, veuillez <a href="seedbox.php">configurer une seedbox.</a></small></h1>
    </div>

<?php } else { ?>
<?php foreach ($transmissions as $value) { ?>

    <ul class="list-group">

    <h3><?php echo $t411->decrypt($value->name); ?> (<?php echo $value->type; ?>)</h3>

<?php $reponse = $t411->listTorrents($value->id);
foreach ($reponse as $key => $torrent) {
  $status = $torrent->isFinished() ? 'success' : 'warning';
?>
     <li style="cursor:pointer" class="list-group-item list-group-item-<?php echo $status;?> title" data-toggle="collapse" data-target="#<?php echo $value->id . $torrent->getHash();?>" href="#<?php echo $value->id . $torrent->getHash();?>">
        <?php echo $torrent->getName() . "\n"; ?>
        <?php echo '<span class="badge progress-bar-' . $status . '">' . ($status == 'warning' ? $torrent->getPercentDone() . '%' : count($torrent->getFiles())) . '</span>' . "\n"; ?>
        <ul class="nav nav-list collapse" id="<?php echo $value->id . $torrent->getHash();?>">
          <span class="label label-info"><?php echo date('d/m/y', $torrent->getStartDate()); ?></span>
          <span class="label label-success"><?php echo $t411->formatBytes($torrent->getSize()); ?> reçu</span>
          <span class="label label-danger"><?php echo $t411->formatBytes($torrent->getUploadedEver());?> envoyé</span>
          <span class="label label-default"><?php echo '(Ratio ' . sprintf('%0.2f', $torrent->getUploadedEver()/$torrent->getSize()); ?>)</span><br>
<?php if ($_COOKIE['username'] == Utils::T411USER) {
foreach ($naslist as $nas) {?>
          <ul>
            <li><a href="download.php?type=hash&id=<?php echo $value->id; ?>&idserv=<?php echo $nas->id; ?>&data=<?php echo $torrent->getHash(); ?>"><span class="glyphicon glyphicon-cloud-download"></span> Envoyer le torrent complet sur <b><?php echo $t411->decrypt($nas->name); ?></b>.</a></li>
<?php foreach ($torrent->getFiles() as $file) { ?>
            <li><a href="download.php?type=link&id=<?php echo $value->id; ?>&idserv=<?php echo $nas->id; ?>&data=<?php echo urlencode($file->getName());?>"><span class="glyphicon glyphicon-download"></span> <?php echo strtr($file->getName(), [$torrent->getName().'/' => '']);?></a> <span class="size">(<?php echo $t411->formatBytes($file->getSize());?>)</span></li>
<?php } ?>
          </ul>
<?php } ?>
<?php } ?>
        </ul>
      </li>
<?php } ?>
    </ul>
<?php } ?>
<?php } ?>
  </div>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  </body>
</html>
