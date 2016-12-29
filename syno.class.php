<?php
/**
 * Implémentation personnelle de l'API Synology DiskStation
 * Fournit les fonctionnalités basiques (connexion,
 * Création des fichiers, téléchargements)
 *
 * @author Matthias BOSC <matthias@bosc.io>
 *
 */
class Syno {
  /**
   * @var string
   */
  public $protocol;

  /**
   * @var string
   */
  public $address;

  /**
   * @var int
   */
  public $port;

  /**
   * @var string
   */
  private $sid;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $destination;

  /**
   * @var string
   */
  public $downloadDir;

  /**
   * @var string
   */
  public $links;

  /**
   * @var string
   */
  private $username;

  /**
   * @var string
   */
  private $password;

  /**
   * @var string
   */
  public $torrent;

  /**
   * @var string
   */
  public $hash;

  /**
   * @var string
   */
  public $basename;

  public function __construct($address, $port) {
    $this->address = $address;
    $this->port = $port;
    $this->protocol = ($port == '5001') ? 'https' : 'http';
  }

  /**
   * Définit les identifiants
   * @return array
   */
  public function setClient($username, $password) {
    $this->username = $username;
    $this->password = $password;
  }

  /**
   * Construit l'URL de base
   * @return string
   */
  private function getBaseUrl() {
    return $this->protocol . '://' . $this->address . '/webapi/';
  }

  /**
   * Obtient le SID pour la session en cours
   * @return array
   */
  public function getSid() {
    $params = [
      'account' => $this->username,
      'passwd' => $this->password,
      'session' => 'DownloadStation',
      'format' => 'sid'
    ];
    return $this->request('auth', 'SYNO.API.Auth', 'login', 3, $params);
  }

  /**
   * Ferme la session pour le SID actuel
   * @return array
   */
  private function closeSession() {
    $params = [
      'session' => 'DownloadStation',
      '_sid' => $this->sid
    ];
    return $this->request('auth', 'SYNO.API.Auth', 'logout', 1, $params);
  }

  /**
   * Cherche le chemin de téléchargement par défaut
   * @return array
   */
  private function getInfo() {
    $params = [
      '_sid' => $this->sid
    ];
    return $this->request('DownloadStation/info', 'SYNO.DownloadStation.Info', 'getconfig', 2, $params);
  }

  /**
   * Créé si besoin un dossier sur le NAS
   * @return array
   */
  private function createFolder() {
    $params = [
      'folder_path' => '/' . $this->folder_path,
      'name' => $this->name,
      '_sid' => $this->sid
    ];
    return $this->request('entry', 'SYNO.FileStation.CreateFolder', 'create', 2, $params);
  }

  /**
   * Créé la tâche de téléchargement sur le NAS distant
   * @return array
   */
  private function download() {
    $params = [
      'api' => 'SYNO.DownloadStation.Task',
      'version' => '1',
      'method' => 'create',
      'uri' => $this->links,
      'destination' => $this->destination,
      '_sid' => $this->sid
    ];
    return $this->request('DownloadStation/', 'task.cgi', 'create', 1, $params, 'post');
  }

  /**
   * Créé la tâche de téléchargement sur le NAS distant
   * @return array
   */
  private function downloadTorrent($filename) {
    $params = [
      'api' => 'SYNO.DownloadStation.Task',
      'version' => '1',
      'method' => 'create',
      'destination' => 'downloads',
      '_sid' => $this->sid,
      'file' => new CurlFile(realpath($filename), 'application/x-bittorrent', $this->name . '.torrent')
    ];
    return $this->request('DownloadStation/', 'task.cgi', 'create', 1, $params, 'post');
  }

  /**
   * Encode les URLs pour être acceptées par DownloadStation
   * @param $string l'URL de base
   * @return string
   */
  public function encode($string) {
    $from = array(' ', "'", '(', ')', ':', ';', '@', '&', '=', '+', '$', ',', '?', '%', '#', '[', ']', '"');
    $to = array('%20', '%27', '%28', '%29', '%3A', '%3B', '%40', '%26','%3D', '%2B', '%24', '%2C', '%3F', '%25', '%23', '%5B', '%5D', '%22');
    return strtr($string, array_combine($from, $to));
  }

  private function writeFile() {
    $torrentfile = tempnam(sys_get_temp_dir(), 'fs1df4sd6f4s6f4s56f4q7er8');
    $handle = fopen($torrentfile, 'w');
    fwrite($handle, base64_decode($this->torrent));
    fclose($handle);
    return $torrentfile;
  }

  public function removeFile($filename) {
    unlink($filename);
  }

  /**
   * Construction des tâches
   */
  public function addTask($type, $data, $name = null) {
    $this->isReacheable();

    if ($type == 'link') {
      $this->links = $data;
    } elseif ($type == 'hash') {
      $this->links = $data;
    } elseif ($type == 'torrent') {
      $this->torrent = $data;
    } else {
      throw new Exception("Erreur inconnue", 1);
    }

    $this->sid = $this->getSid()->data->sid;

    $default_dest = (!empty($this->folder)) ? trim($this->folder, '/') : $this->getInfo()->data->default_destination;

    if ($type == 'hash'  && count(explode(',', $this->links)) > 1) {
      $this->folder_path = $default_dest;
      $this->destination = $default_dest . '/' . $this->name;
      $this->createFolder();
    } else {
      $this->destination = $default_dest;
    }

    if ($type == 'torrent') {
      $file = $this->writeFile();
      $this->downloadTorrent($file);
      $this->removeFile($file);
    } else {
      $this->download();
    }
    $this->closeSession();
  }

  /**
   * Vérifie que la réponse n'est pas vide ou contient une erreur
   * @param array $reponse
   */
  private function checkError($reponse) {
    if (!empty($reponse->error)) {
      throw new Exception('Erreur: ' . $this->error($reponse->error->code), 1);
    } elseif (empty($reponse)) {
      throw new Exception('Pas de réponse', 1);
    } elseif ($reponse->success != 'true') {
      throw new Exception("Réponse invalide", 1);
    }
  }

  /**
   * Avant de lancer les appels à l'API, vérifie que l'hôte distant
   * est bien joignable et répond un code HTTP 200
   */
  public function isReacheable() {
    $curl = curl_init($this->getBaseUrl() . 'auth.cgi');
    curl_setopt($curl, CURLOPT_PORT, $this->port);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    $link = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if (!$link) {
      throw new Exception("Hôte injoignable", 1);
    } elseif ($httpcode != 200) {
      throw new Exception("Réponse invalide", 1);
    }
  }

  /**
   * Construit et éxécute les réquêtes vers le NAS
   * @param $api  le nom du fichier (.cgi) appelé
   * @param $path  le "lien" vers l'API ("DownloadStation", "FileStation", etc.)
   * @param $method  la méthode appelée sur le NAS distant, définie par l'API Synology
   * @param $params  les paramètres passés en header nécessaires pour la requête
   * @param $httpmethod  méthode d'éxécution de la requête (GET ou POST)
   * @return array
   */
  private function request($api, $path, $method, $version = 1, $params = [], $httpmethod = 'get') {
    $url = $this->getBaseUrl().$api.($httpmethod=='get'?'.cgi?api='.$path.'&version='.$version.'&method='.$method.'&'.http_build_query($params):$path);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_PORT, $this->port);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if ($httpmethod == 'post') {
      if (isset($this->torrent)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
      } else {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
      }
    }

    $result = curl_exec($curl);
    curl_close($curl);

    $this->checkError(json_decode($result));
    return json_decode($result);
  }

  /**
   * Retourne un message d'erreur humainement lisible en cas de soucis
   * @param int $code
   * @return string
   */
  private function error($code) {
    switch ($code) {
      case 101:
        $message = 'Paramètre invalide';
        break;
      case 102:
        $message = 'L\'API demandée n\'éxiste pas';
        break;
      case 103:
        $message = 'La méthode demandée n\existe pas';
        break;
      case 104:
        $message = 'La version demandée ne supporte pas cette fontionnalité';
        break;
      case 105:
        $message = 'L\'utilisateur logué n\'a pas la permission';
        break;
      case 106:
        $message = 'Session timeout';
        break;
      case 107:
        $message = 'Session interrupted by duplicate login';
        break;
      case 400:
        $message = 'Compte inexistant ou mot de passe invalide';
        break;
      case 401:
        $message = 'Compte invité désactivé';
        break;
      case 402:
        $message = 'Compte désactivé';
        break;
      case 403:
        $message = 'Mot de passe invalide';
        break;
      case 404:
        $message = 'Permission refusée';
        break;
      default:
        $message = 'Raison inconnue';
        break;
    }
    return $message;
  }
}
?>
