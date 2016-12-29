<?php
require_once 'utils.class.php';
$t411 = new Utils;

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? $_GET['id'] : $t411->home();
$t411->getFullDetails($id);
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>NFO: <?php echo $t411->details->name;?></title>
    <style>
    * { font-family: monospace; font-size: 10px; }
    </style>
  </head>
  <body>
<?php echo $t411->nfo; ?>
  </body>
</html>