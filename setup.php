<?php
require_once __DIR__ . '/' . 'utils.class.php';
$config = new Utils(false);
$fail = null;
$extension = array(
  'curl',
  'ctype',
  'openssl',
  'pdo_mysql',
  'mbstring',
  'xml'
);
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>Configuration de PHP</title>
  </head>
  <body>
<?php
if (version_compare(phpversion(), '5.5', '<')) {
  echo '<p>Version PHP minimale requise : <span style=color:red>5.5</span></p>'; exit;
} else {
  echo '<p>Version PHP: <span style=color:green>OK</span></p>';
}

echo 'Vérifications des extensions:<br>';
foreach($extension as $value) {
  if (extension_loaded($value)) {
    echo '-- Extension ' . $value . ': <span style=color:green>installée</span><br>';
  } else {
    echo '-- Extension ' . $value . ': <span style=color:red>manquante</span><br>';
    $fail = true;
  }
}

if ($fail) { echo "<br>Veuillez <span style=color:red>installer les extensions nécessaires</span> avant de continuer."; exit; }


if (empty(Utils::KEY)) {
  echo "<br>Clé de sécurité: <span style=color:red>non insérée</span><br>";
  echo "Utilisez la clé suivante: " . bin2hex(openssl_random_pseudo_bytes(16))  . '<br>';
  $fail = true;
} elseif (mb_strlen(Utils::KEY, '8bit') != 32) {
  echo "<br>Clé de chiffrement au mauvais format.<br> Veuillez utiliser une clé de 32 octets comme ci-dessous pour un chiffrement 256bits<br>";
  echo "Utilisez la clé suivante: " . bin2hex(openssl_random_pseudo_bytes(16))  . '<br>';
  $fail = true;
} else {
 echo "<br>Clé de sécurité: <span style=color:green>OK</span><br>";
}


echo '<br>';

if (empty(T411::DB_USER) && empty(T411::DB_PASS) && empty(T411::DB_PASS)) {
  echo "Identifiants MySQL: <span style=color:red>non insérés</span><br>";
} else {
  echo "identifiants MySQL: <span style=color:green>insérés</span><br>";
  if ($config->trySQLConnection() === false) {
    echo "-- Connexion MySQL: <span style=color:red>échouée, impossible de continuer.</span><br>";
    exit;
  } else {
    echo "-- Connexion MySQL: <span style=color:green>réussie</span><br>";
    if ($config->createDB()) {
      echo "---- Création de la base de donnée: <span style=color:green>réussie</span><br>";
      $config->createTables();
    } else {
      echo "---- Création de la base de donnée: <span style=color:red>échouée</span><br>";
    }
  }
}

echo "<br><br>Pensez à ajouter les tâches cron nécessaires pour récupérer les tops, et pour le téléchargement automatique (facultatif).<br><pre>";
echo '0 * * * * /usr/bin/php '  . __DIR__ . '/cli/top.php<br>';
echo '0 * * * * /usr/bin/php ' . __DIR__ . '/cli/autodownload.php<br>';
echo '*/10 * * * * /usr/bin/php ' . __DIR__ . '/cli/downloadscheduler.php</pre>';

?>
  </body>
</html>
