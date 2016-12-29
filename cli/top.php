<?php
/**
 * @todo gérer les exception là dedans
 */
require_once __DIR__ . '/../' . 'utils.class.php';
$t411 = new Utils(false);
$t411->order = 'added';

$login = $t411->getLogins();
foreach ($login as $key => $value) {
  if($t411->decrypt($value->t411username) == Utils::T411USER) { $mylogin = $value; }
}

if(empty($mylogin)) {
  echo 'Impossible de récupérer vos identifiants. Vérifiez que la constante T411::T411USER corresponde à votre pseudo T411';
  exit(1);
}

$t411->CliAuth($t411->decrypt($mylogin->t411username), $t411->decrypt($mylogin->t411password));
$t411->getTops();

if(!empty($t411->toptoday) && !empty($t411->topweek) && !empty($t411->topmonth)) {
  $t411->dropDB();
  $t411->updateTopDB('top_day', $t411->toptoday);
  $t411->updateTopDB('top_week', $t411->topweek);
  $t411->updateTopDB('top_month', $t411->topmonth);
} else { echo 'fail'; exit(1); }
?>
