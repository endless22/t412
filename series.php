<?php
require_once 'utils.class.php';
$t411 = new Utils;
$t411->order = 'size';
$saison = isset($_GET['saison']) && ctype_digit($_GET['saison']) ? sprintf('%02d', $_GET['saison']) : null;
$search = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8')) : null;
$langage = isset($_GET['langue']) && ctype_digit($_GET['langue']) ? $_GET['langue'] : null;
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>t412 | Séries</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="/">Recherche</a></li>
      <li><a><?php echo $search; ?></a></li>
    </ol>

    <div class="row">
      <form action="/series.php" method="get">
        <div class="col-xs-7 col-md-3 col-lg-4">
          <input type="text" class="form-control" name="search" placeholder="<?php echo isset($search) ? $search : "Nom de la série"; ?>" value="<?php echo $search; ?>" required autofocus>
        </div>

        <div class="col-xs-5 col-md-3 col-lg-2">
          <input type="tel" pattern="[0-9 ]*" title="Chiffres uniquement." class="form-control" name="saison" maxlength="2" placeholder="<?php echo isset($saison) ? $saison : "Saison"; ?>" value="<?php echo $saison; ?>" required>
        </div>

        <div class="col-xs-7 col-md-3 col-lg-2 po">
          <select name="langue" class="form-control">
            <option <?php if($langage == '1210'){echo 'selected ';}?>value="1210">VFF</option>
            <option <?php if($langage == '1209'){echo 'selected ';}?>value="1209">VO</option>
            <option <?php if($langage == '1212'){echo 'selected ';}?>value="1212">Multi (VO/VFF)</option>
            <option <?php if($langage == '1216'){echo 'selected ';}?>value="1216">VOST/FR</option>
          </select>
        </div>

        <div class="col-xs-5 col-md-3 col-lg-4 po">
          <button class="btn btn-primary btn-block" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Rechercher</button>
        </div>
      </form>
    </div>

<?php if(empty($search)) { ?>

    <div class="jumbotron">
      <h1><small style="color:red">Rechercher une série.</small></h1>
      <p>Les résultats seront affichés ici, avec la meilleure qualité disponible.</p>
    </div>

<?php } else {

$t411->serie = $search;
$t411->saison = $saison;
$t411->langage = $langage;
$t411->tvShowSearch();
if (empty($t411->tvsearch) && empty($t411->error)) { ?>

    <div class="jumbotron">
      <h1><small style="color:red">Sad panda !</small></h1>
      <p>La recherche n'a retourné aucun résultat.</p>
    </div>

<?php } elseif (!empty($t411->error)) { ?>

    <div class="jumbotron">
      <h1><small style="color:red">Erreur !</small></h1>
      <p>Les serveurs de <a href="https://www.t411.ch">T411</a> mettent trop de temps à répondre. Pensez à vérifier <a href="http://irc.t411.ch/ip/index.php">l'état du tracker</a>.</p>
    </div>

<?php } else {

$a = $s = $size = $torrents = [];

foreach ($t411->tvsearch as $key => $value) {
  for ($i = 1; $i <= 24; $i++) {
    $i = $i < 10 ? 0 . $i : $i;
    (stripos($value->name, 'S'.$saison.'E'.$i) !== false) && $value->size < 3000000000 ? $a[$key]=(array)$value AND $a[$key]['episode']=$i : null;
  }
}

foreach ($a as $key => $value) {
  !array_keys(array_column($torrents, 'episode'), $value['episode']) && (stripos($t411->cleanTitle($value['name']), $t411->cleanTitle(urldecode($search)) . '.S' . $saison) !== false) && (stripos($value['name'], '1080') !== false) ? $torrents[$value['episode']] = $value : null;
}

foreach ($a as $key => $value) {
  !array_keys(array_column($torrents, 'episode'), $value['episode']) && (stripos($t411->cleanTitle($value['name']), $t411->cleanTitle(urldecode($search)) . '.S' . $saison) !== false) && (stripos($value['name'], '720') !== false) ? $torrents[$value['episode']] = $value : null;
}

foreach ($a as $key => $value) {
  !array_keys(array_column($torrents, 'episode'), $value['episode']) ? $torrents[$value['episode']] = $value AND $torrents[$value['episode']]['fallback'] = 'true' : null;
}

array_multisort(array_column($torrents, 'episode'), SORT_DESC, $torrents);
?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <caption>Épisode par épisode</caption>
        <thead>
          <tr>
            <th class="textcentered">Épisode</span></a></th>
            <th>Nom</th>
            <th class="textcentered">Age</th>
            <th class="textcentered">Taille</th>
            <th class="textcentered">Complété</th>
            <th class="textcentered">Seeders</th>
            <th class="textcentered">Leechers</th>
          </tr>
        </thead>
        <tbody>

<?php foreach ($torrents as $value) { ?>
          <tr>
            <td nowrap class="textcentered"><?php echo $value['episode']; if(!empty($value['fallback'])) { echo ' <span class="glyphicon glyphicon-warning-sign" title="Le résultat peut légèrement différer de la recherche"></span>';}?></td>
            <td><a href="/details/<?php echo $value['id'];?>"><?php echo $value['name'];?></a></td>
            <td nowrap class="textcentered"><?php echo $t411->humanTiming(strtotime($value['added']));?></td>
            <td nowrap class="textcentered"><?php echo $t411->formatBytes($value['size']);?></td>
            <td class="textcentered"><?php echo $value['times_completed'];?></td>
            <td class="seeders textcentered"><?php echo $value['seeders'];?></td>
            <td class="leechers textcentered"><?php echo $value['leechers'];?></td>
          </tr>
<?php } ?>

        </tbody>
      </table>
    </div>

<?php } if(!empty($t411->tvpack)) { ?>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <caption>Pack saison complète</caption>
        <thead>
          <tr>
            <th class="textcentered">Saison</th>
            <th>Nom</th>
            <th class="textcentered">Age</th>
            <th class="textcentered">Taille</th>
            <th class="textcentered">Complété</th>
            <th class="textcentered">Seeders</th>
            <th class="textcentered">Leechers</th>
          </tr>
        </thead>
        <tbody>

<?php foreach ($t411->tvpack as $value) { ?>
          <tr>
            <td nowrap class="textcentered"><?php echo $saison;?></td>
            <td><a href="/details/<?php echo $value->id;?>"><?php echo $value->name;?></a></td>
            <td nowrap class="textcentered"><?php echo $t411->humanTiming(strtotime($value->added));?></td>
            <td nowrap class="textcentered"><?php echo $t411->formatBytes($value->size);?></td>
            <td class="textcentered"><?php echo $value->times_completed;?></td>
            <td class="seeders textcentered"><?php echo $value->seeders;?></td>
            <td class="leechers textcentered"><?php echo $value->leechers;?></td>
          </tr>
<?php } ?>

        </tbody>
      </table>
    </div>

<?php
    }
  }
 ?>
  </div>
  <script src="/js/jquery.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
  </body>
</html>
