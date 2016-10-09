<?php
require_once 'utils.class.php';
$t411 = new Utils;

$t411->id = isset($_GET['id']) && ctype_digit($_GET['id']) ? $_GET['id'] : $t411->home();
$t411->getDetails();
print_r($t411->nfo);
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
