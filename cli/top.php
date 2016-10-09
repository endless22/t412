<?php
require_once __DIR__ . '/../' . 'utils.class.php';
$t411 = new Utils(false);
$t411->order = 'added';

$login = $t411->getLogins();
foreach ($login as $key => $value) {
  if($t411->dechiffrer($value->t411user) == Utils::T411USER) { $mylogin = $value; }
}

$t411->CliAuth($t411->dechiffrer($mylogin->t411user), $t411->dechiffrer($mylogin->t411pass));
$t411->getTops();

if(!empty($t411->toptoday) && !empty($t411->topweek) && !empty($t411->topmonth)) {
  $t411->updateTopDB('dailytop', $t411->toptoday);
  $t411->updateTopDB('weeklytop', $t411->topweek);
  $t411->updateTopDB('monthlytop', $t411->topmonth);
} else { echo 'fail'; }
?>
