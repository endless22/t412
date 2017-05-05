<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;
$search = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'], ENT_COMPAT, 'UTF-8')) : null;
$catid = isset($_GET['cat']) && ctype_digit($_GET['cat']) ? $_GET['cat'] : null;
$order = isset($_GET['order']) && ctype_alnum(str_replace(['-', '_'], '', $_GET['order'])) ? $_GET['order'] : null;
$type = isset($_GET['type']) && ctype_alpha($_GET['type']) ? $_GET['type'] : null;
$page = isset($_GET['page']) && ctype_digit($_GET['page']) ? $_GET['page'] : 1;
$t411->querystring =  isset($_GET['cat']) && ctype_digit($_GET['cat']) ? $_GET['cat'] : null;
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>t412 - <?php echo isset($search) ? $search : 'Accueil'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/navbar.css">
  </head>
  <body>

  <div class="container">
<?php require_once __DIR__ . '/' . 'navbar.php'; ?>

    <ol class="breadcrumb">
      <li><a href="/">Recherche</a></li>
      <li><a><?php echo $search; ?></a></li>
    </ol>

    <div class="row">
      <form action="index.php" class="validate-form" method="get">
        <div class="col-xs-7 col-md-4 col-lg-6">
          <input type="text" class="form-control" name="search" placeholder="<?php echo isset($search) ? $search : "Rechercher un torrent"; ?>" value="<?php echo $search; ?>" required autofocus>
        </div>

        <div class="col-xs-5 col-md-4 col-lg-2">
          <select name="cat" class="form-control">
            <option value="">Catégorie</option>
            <option <?php if ($catid == '623'){echo 'selected ';}?>value="623">Musique</option>
            <option <?php if ($catid == '631'){echo 'selected ';}?>value="631">Film</option>
            <option <?php if ($catid == '433'){echo 'selected ';}?>value="433">Série</option>
            <option <?php if ($catid == '633'){echo 'selected ';}?>value="633">Concert</option>
            <option <?php if ($catid == '635'){echo 'selected ';}?>value="635">Spectacle</option>
            <option <?php if ($catid == '639'){echo 'selected ';}?>value="639">Émission TV</option>
            <option <?php if ($catid == '236'){echo 'selected ';}?>value="236">Windows</option>
            <option <?php if ($catid == '246'){echo 'selected ';}?>value="246">Jeu</option>
            <option <?php if ($catid == '632'){echo 'selected ';}?>value="632">xXx</option>
          </select>
        </div>

        <div class="col-xs-12 col-md-4 po">
          <button class="btn btn-primary btn-block" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Rechercher</button>
        </div>
      </form>
    </div>

<?php if (empty($search)) { ?>

    <div class="jumbotron">
      <h1><small style="color:red">Rechercher un torrent.</small></h1>
      <p>Les résultats seront affichés ici.</p>
    </div>

<?php } else {

$t411->query = $search;
$t411->sort_order = ($type == 'asc') ? 4 : 3;
$t411->order = $order;
$t411->torrentSearch();
if (empty($t411->search) && empty($t411->error)) { ?>

    <div class="jumbotron">
      <h1><small style="color:red">Sad panda !</small></h1>
      <p>La recherche n'a retourné aucun résultat.</p>
    </div>

<?php } elseif (!empty($t411->error)) { ?>

    <div class="jumbotron">
      <h1><small style="color:red">Erreur !</small></h1>
      <p>Les serveurs de <a href="https://www.t411.al">T411</a> mettent trop de temps à répondre. Pensez à vérifier <a href="http://irc.t411.ch/ip/index.php">l'état du tracker</a>.</p>
    </div>

<?php } else { $array = array_slice($t411->search, ($page-1)*50, 50, true); ?>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th class="textcentered"><a href="index.php?search=<?php echo $search;?>&cat=<?php echo $catid;?>&order=categoryname&type=<?php echo $t411->setordertype($order, 'categoryname', $type);?>">Type</a></th>
            <th><a href="index.php?search=<?php echo $search;?>&cat=<?php echo $catid?>&order=name&type=<?php echo $t411->setordertype($order, 'name', $type);?>">Nom</a></th>
            <th class="textcentered"><a href="index.php?search=<?php echo $search;?>&cat=<?php echo $catid;?>&order=added&type=<?php echo $t411->setordertype($order, 'added', $type);?>">Age</a></th>
            <th class="textcentered"><a href="index.php?search=<?php echo $search;?>&cat=<?php echo $catid;?>&order=size&type=<?php echo $t411->setordertype($order, 'size', $type);?>">Taille</a></th>
            <th class="textcentered"><a href="index.php?search=<?php echo $search;?>&cat=<?php echo $catid;?>&order=times_completed&type=<?php echo $t411->setordertype($order, 'times_completed', $type);?>">Complété</a></th>
            <th class="textcentered"><a href="index.php?search=<?php echo $search;?>&cat=<?php echo $catid;?>&order=seeders&type=<?php echo $t411->setordertype($order, 'seeders', $type);?>">Seeders</a></th>
            <th class="textcentered"><a href="index.php?search=<?php echo $search;?>&cat=<?php echo $catid;?>-&order=leechers&type=<?php echo $t411->setordertype($order, 'leechers', $type);?>">Leechers</a></th>
          </tr>
        </thead>
        <tbody>

<?php foreach ($array as $value) { if (!empty($value->name)) { ?>
          <tr>
            <td nowrap class="textcentered"><?php echo $value->categoryname;?></td>
            <td><a href="details.php?id=<?php echo $value->id;?>"><?php echo $value->name;?></a></td>
            <td nowrap class="textcentered"><?php echo $t411->humanTiming(strtotime($value->added));?></td>
            <td nowrap class="textcentered"><?php echo $t411->formatBytes($value->size);?></td>
            <td class="textcentered"><?php echo $value->times_completed;?></td>
            <td class="seeders textcentered"><?php echo $value->seeders;?></td>
            <td class="leechers textcentered"><?php echo $value->leechers;?></td>
          </tr>
<?php } } ?>

        </tbody>
      </table>
    </div>
<?php
if (count($t411->search) > 50) {
  $args = 'search='.$search.(empty($catid)?null:'&cat='.$catid).
    (empty($order)?null:'&order='.$order).(empty($type)?null:'&type='.$type);
?>
    <nav class="pull-right">
      <ul class="pagination">
        <li>
          <a href="<?php echo "index.php?$args&page=1"; ?>" aria-label="First">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
<?php echo $t411->paginate($page, count($t411->search), $args); ?>
        <li>
          <a href="<?php echo "index.php?$args&page=" . ceil(count($t411->search)/50); ?>" aria-label="Last">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul>
    </nav>
<?php
}
}
}
?>

  </div>
  <script src="js/jquery.min.js"></script>
  <script src="js/validate.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>
</html>
