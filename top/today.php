<?php
require_once __DIR__ . '/../' . 'utils.class.php';
$t411 = new Utils;
$t411->top = $t411->getTopFromDB('dailytop');

$categories = array(
  'Audio' => array(
    400 => 'Karaoke',
    403 => 'Samples',
    623 => 'Musique',
    642 => 'Podcast Radio'
  ),
  'eBook' => array(
    405 => 'Audio',
    406 => 'Bds',
    407 => 'Comics',
    408 => 'Livres',
    409 => 'Mangas',
    410 => 'Presse'
  ),
  'Jeu Vidéo' => array(
    239 => 'Linux',
    245 => 'MacOS',
    246 => 'Windows',
    307 => 'Nintendo',
    308 => 'Sony',
    309 => 'Microsoft',
    626 => 'Smartphone',
    628 => 'Tablette',
    630 => 'Autre'
  ),
  'Émulation' => array(
    342 => 'Émulateurs',
    344 => 'Roms'
  ),
  'GPS' => array(
    391 => 'Application',
    393 => 'Cartes',
    394 => 'Divers'
  ),
  'Application' => array(
    242 => 'Linux',
    235 => 'MacOS',
    236 => 'Windows',
    625 => 'Smartphone',
    627 => 'Tablette',
    629 => 'Autre',
    638 => 'Formation'
  ),
  'Film/Vidéo' => array(
    402 => 'Vidéo-clips',
    433 => 'Série TV',
    455 => 'Animation',
    631 => 'Film',
    633 => 'Concert',
    634 => 'Documentaire',
    635 => 'Spectacle',
    636 => 'Sport',
    637 => 'Animation Série',
    639 => 'Émission TV'
  ),
  'xXx' => array(
    461 => 'eBooks',
    462 => 'Jeux Vidéo',
    /** facepalm */
    632 => 'Video',
    641 => 'Animation'
    /** double facepalm */
  )
);
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>t412 | top jour</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/../' . 'navbar.php'; ?>

<?php if(empty($t411->top)) { ?>

    <div class="jumbotron">
      <h1><small style="color:red">Erreur !</small></h1>
      <p>La base <i>dailytop</i> est vide. Pensez à <strong>ajouter les tâches cron</strong> sur votre serveur!.</p>
    </div>

<?php } else {

$array = array();

foreach ($categories as $topcategory => $subcategories) {
  foreach ($subcategories as $code => $category) {
    foreach ($t411->top as $key => $value) {
      if($value->category == $code) { $array[$topcategory][$category][] = $value; }
    }
  }
}

foreach ($array as $topcategory => $subcategories) {
  echo '<ol class="breadcrumb"><li><a href="#'.$topcategory.'">'.$topcategory.'</a></li></ol>';
  foreach ($subcategories as $code => $category) {
?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <caption class="hidden-sm hidden-md hidden-xs"><?php echo $code; ?></caption>
        <thead>
          <tr>
            <th class="textcentered">Type</th>
            <th>Nom</a></th>
            <th class="textcentered">Age</th>
            <th class="textcentered">Taille</th>
            <th class="textcentered">Complété</th>
            <th class="textcentered">Seeders</th>
            <th class="textcentered">Leechers</th>
          </tr>
        </thead>
<?php foreach ($category as $torrents) { ?>
        <tbody>
          <tr>
            <td nowrap class="textcentered"><?php echo $torrents->categoryname; ?></td>
            <td><a href="/details/<?php echo $torrents->id; ?>"><?php echo $torrents->name; ?></a></td>
            <td nowrap class="textcentered"><?php echo $t411->humanTiming(strtotime($torrents->added)); ?></td>
            <td nowrap class="textcentered"><?php echo  $t411->formatBytes($torrents->size); ?></td>
            <td class="textcentered"><?php echo $torrents->times_completed; ?></td>
            <td class="seeders textcentered"><?php echo $torrents->seeders; ?></td>
            <td class="leechers textcentered"><?php echo $torrents->leechers; ?></td>
          </tr>
        </tbody>

<?php
    } ?>
      </table>
    </div>
<?php
  }
}
}
?>

  </div>
  <script src="/js/jquery.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
</body>
</html>