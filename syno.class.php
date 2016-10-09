<?php
require_once __DIR__ . '/' . 'utils.class.php';
/**
 * The Syno class is used to make API calls to the remote Synology NAS
 *
 * @author Matthias BOSC <matthias@bosc.io>
 * @todo handle errors, and more !
 */
class Syno extends Utils {
  /**
   * @var string
   */
  public $protocol = 'http';
  /**
   * @var string
   */
  public $ip;

  /**
   * @var int
   */
  public $port = 5000;

  /**
   * @var string
   */
  public $user;

  /**
   * @var string
   */
  public $pass;

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
   * @var array
   */
  private $opts = [];

  /**
   * @var string
   */
  public $error = null;

  /**
   * @var string
   */
  public $reponse;

  public function __construct($hash = null, $link = null) {
    Utils::__construct();
    $this->hash = !empty($hash) ? $hash : null;
    $this->links = empty($link) ? $this->getTorrentInfo($hash) : parent::DL_PREFIX.$this->encode($link);
  }

  /**
   * Construit l'URL de base
   * @return string
   */
  private function getBaseUrl() {
    return $this->protocol . '://' . $this->ip . '/webapi/';
  }

  public function getTorrentInfo($hash) {
    $this->reponse = $this->listTorrent($hash);
    return $this->getLinks();
  }

  private function getLinks() {
    return implode(',', array_map(function($uri) { return parent::DL_PREFIX.$this->encode($uri); }, array_column($this->reponse->files, 'name')));
  }

  /**
   * Obtient le SID pour la session en cours
   * @return array
   */
  private function getSid() {
    $params = [
      'account' => $this->user,
      'passwd' => $this->pass,
      'session' => 'DownloadStation',
      'format' => 'sid'
    ];
    return $this->request('auth', 'SYNO.API.Auth', 'login', 3, $params);
  }

  /**
   * Ferme la session pour le SID actuel
   * @return string
   */
  private function closeSession() {
    $params = [
      'session' => 'DownloadStation',
      '_sid' => $this->sid
    ];
    return $this->request('auth', 'SYNO.API.Auth', 'logout', 1, $params);
  }

  /**
   * Cherche le chemin de téléchargement
   * @return string
   */
  private function getInfo() {
    $params = [
      '_sid' => $this->sid
    ];
    return $this->request('DownloadStation/info', 'SYNO.DownloadStation.Info', 'getconfig', 2, $params);
  }

  /**
   * Vérifie si le fichier à télécharger est dans un dossier
   * Si c'est le cas - mais - qu'il y a un seul fichier dans le dossier
   * Le téléchargement se fera à la racine
   * @return bool
   */
  private function isDir() {
    return count(explode(',', $this->links)) > 1 ? is_dir($this->downloadDir . '/' . $this->name) : false;
  }

  private function isShow() {
    return preg_match("'^(.+)S([0-9]+)E([0-9]+).*$'i", basename($this->links)) ? true : false;
  }

  private function buildPath() {
    preg_match("'^(.+)S([0-9]+)E([0-9]+).*$'i", basename(urldecode($this->links)), $output);
    $name = strtr(rtrim($output[1], ' .-_;'), '.,;-_', '     ');
    $name = ctype_alpha(strtr($name, [' ' => ''])) ? $name : null;
    $saison = ctype_digit($output[2]) ? intval($output[2],10) : null;
    return (!empty($name) && !empty($saison)) ? strtolower('series/' . $name . '/saison ' . $saison) : 'downloads';
  }

  private function encode($string) {
    $from = [' ', "'", '(', ')', ':', ';', '@', '&', '=', '+', '$', ',', '?', '%', '#', '[', ']', '"'];
    $to = ['%20', '%27', '%28', '%29', '%3A', '%3B', '%40', '%26','%3D', '%2B', '%24', '%2C', '%3F', '%25', '%23', '%5B', '%5D', '%22'];
    return strtr($string, array_combine($from, $to));
  }

  /**
   * Créé si besoin un dossier sur le NAS
   * @return string
   */
  private function createFolder() {
    $params = [
      'folder_path' => '/' . $this->destination,
      'name' => $this->name,
      '_sid' => $this->sid
    ];
    return $this->request('entry', 'SYNO.FileStation.CreateFolder', 'create', 2, $params);
  }

  /**
   * Télécharge les fichiers sur le nas
   * @return string
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
    $this->closeSession();
  }

  /**
   * Créé les tâches
   * @return string
   */
  public function addFromHash() {
    if($this->reponse->status->status != '6') {
      $this->error = 'Fichier incomplet';
    } else {
      $this->downloadDir = $this->reponse->downloadDir;
      $this->name = $this->reponse->name;
      $this->sid = $this->getSid()->data->sid ?? null;
      if(empty($this->error)) {
        $this->destination = $this->getInfo()->data->default_destination;
        $this->isDir() ? $this->createFolder() : null;
        $this->destination = $this->isDir() ? $this->destination . '/' . $this->name : null;
        $this->download();
      }
    }
  }

  public function addFromUrl() {
    $this->name = urldecode(basename($this->links));
    $this->sid = $this->getSid()->data->sid ?? null;
    if(empty($this->error)) {
      $this->destination = $this->isShow() ? $this->buildPath() : $this->getInfo()->data->default_destination;
      $this->download();
    }
  }

  public function buildTask() {
    return !empty($this->hash) ? $this->addFromHash() : $this->addFromUrl();
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
        $message = 'Compte guest désactivé';
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
        $message = 'Erreur inconnue';
        break;
    }
    return $message;
  }

  /**
   * Vérifie que la réponse n'est pas vide ou contient une erreur
   * @param object $reponse
   * @return Ambigus <array,null>
   */
  private function checkError($reponse) {
    if(!empty($reponse->error) && empty($this->error)) {
      $this->error = $this->error($reponse->error->code);
    } elseif(empty($reponse) && empty($this->error)) {
      $this->error = 'Pas de réponse';
    }
  }

  /**
   * Construit et éxécute les réquêtes vers le NAS
   * @return array
   */
  private function request($api, $path, $method, $version = 1, $params = [], $httpmethod = 'get') {
    $url = $this->getBaseUrl().$api.($httpmethod=='get'?'.cgi?api='.$path.'&version='.$version.'&method='.$method.'&'.http_build_query($params):$path);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_PORT, $this->port);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if($httpmethod == 'post') {
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    if($result = curl_exec($curl)) { } else {
      $this->error = curl_error($curl);
    }
    curl_close($curl);

    $this->checkError(json_decode($result));
    return json_decode($result);
  }
}
?>
