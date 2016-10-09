<?php
require_once __DIR__ . '/' . 'transmission.class.php';

class Utils extends Transmission2 {
  public function login() {
    header('Location: /login/');
    exit;
  }

  public function logout() {
    header('Location: /logout/');
    exit;
  }

  public function home() {
    header('Location: /index.php');
    exit;
  }

  /**
   * extrait l'uid du token
   */
  public function getUid($token) {
    $userid = explode(':', $token);
    return ctype_digit($userid[0]) ? $userid[0] : $this->logout();
  }

  /**
   * Retourne la pagination requise pour la recherche
   */
  public function paginate($current, $total, $args) {
    $start = $current > 3 ? $current-2 : 1;
    $end = $total >= ($current+2)*50 ? $start+4 : ceil($total/50);
    for ($i = $start; $i <= $end; $i++) {
      echo '          <li' . ($current == $i ? ' class="active"' : null) . '><a href="/index.php?'.$args.'&page='.$i.'">'.$i.'</a></li>'."\n";
    }
  }

  /**
   * Convertit une valeur brute (bytes) en poids humainement lisible
   * Source: http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
   */
  public static function formatBytes($bytes) {
    $units = array('o', 'Ko', 'Mo', 'Go', 'To');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, 2) . ' ' . $units[$pow];
  }

  public function setordertype($order, $categoryname, $type) {
    return (isset($_GET['type']) && $order == $categoryname && $type == 'desc') ? 'asc' : 'desc';
  }

  /**
   * Convertit un timestamp en temps écoulé, humainement lisible
   * Source: http://stackoverflow.com/questions/2915864/php-how-to-find-the-time-elapsed-since-a-date-time
   */
  public function humanTiming($time) {
    $time = time() - $time;
    $time = $time < 1 ? 1 : $time;
    $tokens = [
      31536000 => 'an',
      2592000 => 'mois',
      604800 => 'semaine',
      86400 => 'jour',
      3600 => 'heure',
      60 => 'minute',
      1 => 'seconde'
    ];

    foreach ($tokens as $unit => $text) {
      if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
    return ($text == 'mois') ? $numberOfUnits.' ' .$text : $numberOfUnits . ' ' . $text.(($numberOfUnits>1)?'s':'');
    }
  }

  /**
   * Fonctions pour chiffer/déchiffrer le contenu enregistré en base
   * Ce n'est pas la solution idéale, mais je ne peux pas hasher le contenu
   * Puisque il doit être déchiffré pour lancer les requêtes
   *
   * Source: stackoverflow.com/questions/1289061/best-way-to-use-php-to-encrypt-and-decrypt-passwords
   */
  public function chiffrer($string) {
    $iv = mcrypt_create_iv(
      mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC),
      MCRYPT_DEV_URANDOM
    );

    $encrypted = base64_encode(
      $iv .
      mcrypt_encrypt(
        MCRYPT_RIJNDAEL_128,
        hash('sha256', pack('H*', parent::KEY), true),
        $string,
        MCRYPT_MODE_CBC,
        $iv
      )
    );
  return $encrypted;
  }

  public function dechiffrer($string) {
    $data = base64_decode($string);
    $iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));

    $decrypted = rtrim(
    mcrypt_decrypt(
      MCRYPT_RIJNDAEL_128,
      hash('sha256', pack('H*', parent::KEY), true),
      substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)),
      MCRYPT_MODE_CBC,
      $iv
    ),
    "\0"
  );
  return $decrypted;
  }
}

?>
