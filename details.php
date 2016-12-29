<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? $_GET['id'] : $t411->home();
$t411->getFullDetails($id);
$servers = $t411->getSeedboxes();

if (!empty($t411->details->name)) {
  foreach ($t411->search->torrents as $value) {
    if ($value->name == $t411->details->name) { $info = $value; break; }
  }
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title><?php echo isset($t411->details->name)  ? $t411->details->name : 'Détails';?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/details.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

<?php if (empty($t411->details->name)) { ?>

    <div class="jumbotron">
      <h1 style="color:red">Erreur !</h1>
      <p>Aucune donnée trouvée. <a href="details.php?id=<?php echo $id; ?>">Rafraîchir la page</a> ou retourner à <a href="index.php">l'accueil</a>.</p>
    </div>

<?php } else { ?>

    <ol class="breadcrumb">
      <li><a href="index.php">Torrent</a></li>
      <li><a><?php echo $t411->details->categoryname; ?></a></li>
      <li class="active title"><a href="details.php?id=<?php echo $id; ?>"><?php echo strtr($t411->details->name, '.', ' '); ?></a></li>
    </ol>

<?php if (!empty($servers)) { foreach ($servers as $value) { if (($value->type == 'transmission') && ($t411->listTorrent($value->id, $t411->hash) !== false)) { ?>
    <div class="alert alert-info alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <strong>Instantané:</strong> Vous avez déjà téléchargé ce torrent!
    </div>
<?php break; } } } ?>

<?php if (!empty($t411->comments) && $t411->flagged === true) { ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <strong>Instantané:</strong> Des commentaire font allusions à <strong>Hadopi</strong>, prenez garde.
    </div>
<?php } ?>

    <div class="btn-group btn-group-justified" role="group">
      <a class="btn btn-default" role="button" href="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';?>">Retour</a>
      <a class="btn btn-default" role="button" href="nfo.php?id=<?php echo $id; ?>" target="_blank">Voir le NFO</a>
      <div class=btn-group role=group>
        <a href=# class="btn btn-default dropdown-toggle" data-toggle=dropdown role=button aria-haspopup=true aria-expanded=false> Télécharger <span class=caret></span> </a>
        <ul class="dropdown-menu dropdown-menu-right">
<?php if (!empty($servers)) { ?>
<?php foreach ($servers as $value) { ?>
          <li><a href="download.php?type=torrent&id=<?php echo $id;?>&idserv=<?php echo $value->id;?>">Envoyer sur <b><?php echo $t411->decrypt($value->name); ?></b></a></li>
<?php } ?>
<?php } ?>
          <li><a href="directdownload.php?id=<?php echo $id;?>" target="_blank">Télécharger le fichier <b>.torrent</b></a></li>
        </ul>
      </div>

    </div>

    <div class="page-header terms">
<?php if (!isset($info)) { ?>
      <span class="label label-primary">Informations indisponible</span>
<?php } else { ?>
      <span class="label label-primary"><?php echo $t411->formatBytes($info->size);?></span>
      <span class="label label-primary"><?php echo date_format(date_create($info->added), 'd/m/Y'); ?></span>
      <span class="label label-primary"><?php echo $info->times_completed; ?> Téléchargements</span>
      <span class="label label-success"><?php echo $info->seeders; ?> seeders</span>
      <span class="label label-danger"><?php echo $info->leechers; ?> leechers</span>
<?php } foreach ($t411->details->terms as $value) { echo '      <span class="label label-default">'.$value.'</span>'."\n"; } ?>
    </div>

    <div class="detail">
      <?php echo $t411->details->description . "\n"; ?>
    </div>

<?php if (!empty($t411->comments)) {
    foreach ($t411->comments as $value) { ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><?php echo $value->pseudo . ' (' . $value->date; ?>)</h3>
      </div>
      <div class="panel-body">
        <?php echo $value->texte . "\n"; ?>
      </div>
    </div>
<?php } } ?>
    </div>
<?php } ?>
  </div>
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  </body>
</html>