<?php
require_once 't412.class.php';

/**
 * Classe utilisée pour envoyer toutes les requêtes vers l'API de T411
 * Les méthodes sont uniques et non réutilisables dans le but d'optimiser
 * le temps de chargement
 *
 * Le plupart des requêtes s'éxécutent simultanément
 * Ce qui diminue le temps de chargement de la page
 */
class Torrent extends T411 {

  public $reponse;
  public $details;
  public $web;
  public $base64;
  public $query;
  public $querystring;
  public $userinfo;
  public $top;
  public $tvsearch;
  public $tvpack;
  public $langage;
  public $saison;
  public $serie = null;

  function __construct($connected = true) {
    T411::__construct($connected);
  }

  /**
   * Envoie une requête POST sur l'api T411 avec les identifiants récupéres sur la page de login
   * Si la requête réussit, stocke le token dans un cookie et passe à la suite
   *
   * @param string $user
   * @param string $pass
   * @param int $ttl temps de validité du token
   */
  public function Auth($user, $pass, $ttl) {
    $ch = curl_init(self::API_URL . '/auth');

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
      http_build_query(
        [
          'username' => $user,
          'password' => $pass
        ]
      ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $this->reponse = json_decode(curl_exec($ch));
    if(array_key_exists('token', $this->reponse)) {
      setcookie('token', $this->reponse->token, $ttl, '/');
      $this->storeCredentials($this->getUid($this->reponse->token), $this->chiffrer($user), $this->chiffrer($pass));
      header('Location: /');
    } else {
      return isset($this->reponse->error) ? $this->reponse->error : 'erreur';
      header('Location: /login/');
    }
    exit;
  }

  /**
   * Connexion en CLI, envoie une requête POST sur l'api T411 avec les identifiants récupéres en base
   * Si la requête réussit, stocke le token et l'uid dans un objet
   *
   * @param string $user
   * @param string $pass
   */
  public function CliAuth($user, $pass) {
    $ch = curl_init(self::API_URL . '/auth');

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
      http_build_query(
        [
          'username' => $user,
          'password' => $pass
        ]
      ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $this->reponse = json_decode(curl_exec($ch));
    if(array_key_exists('token', $this->reponse)) {
      $this->token = $this->reponse->token;
      $this->uid = $this->getUid($this->token);
    }
  }

  /** @todo all here */
  public function getUserInfo() {
    $ch = curl_init(self::API_URL . '/users/profile/' . $this->uid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    $result = curl_exec($ch);
    $this->userinfo = json_decode($result);
  }

  /** @todo all here */
  public function getTree() {
    $ch = curl_init(self::API_URL . '/categories/tree/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    $result = curl_exec($ch);
    return json_decode($result);
  }


  /**
   * Envoie le requête de recherche avec les arguments donnés
   * Limite laissée à 5000 par défaut
   */
  public function torrentSearch() {
    $ch = curl_init(self::API_URL . '/torrents/search/' . urlencode($this->query) . '?limit=5000&cid=' . $this->querystring);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    $result = curl_exec($ch);
    $this->search = $this->cleanArray($this->standarsize(json_decode($result)));
    $this->search = empty($this->order) ? $this->search : $this->sortArray($this->search);
  }


  /**
   * Fonction utilisée pour effectuer une recherche sur une série
   * Deux requêtes GET sont envoyées simultanément, une cherchant tous les épisodes
   * Correspondant à la saison demandée l'autre le pack intégral
   */
  public function tvShowSearch() {
    $ch1 = curl_init(self::API_URL . '/torrents/search/' . urlencode($this->serie) . '?limit=5000&cid=433&term[51][]=' . $this->langage . '&term[45][]=' . (967+$this->saison));
    $ch2 = curl_init(self::API_URL . '/torrents/search/' . urlencode($this->serie) . '?limit=5000&cid=433&term[46][]=936&term[45][]=' . (967+$this->saison));
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);

    $mh = curl_multi_init();
    curl_multi_add_handle($mh, $ch1);
    curl_multi_add_handle($mh, $ch2);

    // éxécute toutes les requêtes simultanément
    $running = null;
    do {
      curl_multi_exec($mh, $running);
    } while ($running);

    $this->tvsearch = $this->sortArray($this->standarsize(json_decode(curl_multi_getcontent($ch1))));
    $this->tvpack = $this->sortArray($this->standarsize(json_decode(curl_multi_getcontent($ch2))));
  }


  /**
   * Récupère les tops (jour, semaine, mois) simultanément, et les envoie en base
   * GET /torrents/top/{frequence}
   */
  public function getTops() {
    // construit les requêtes individuellement, sans les éxécuter
    $ch1 = curl_init(self::API_URL . '/torrents/top/today');
    $ch2 = curl_init(self::API_URL . '/torrents/top/week');
    $ch3 = curl_init(self::API_URL . '/torrents/top/month');
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    curl_setopt($ch3, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);

    $mh = curl_multi_init();
    curl_multi_add_handle($mh, $ch1);
    curl_multi_add_handle($mh, $ch2);
    curl_multi_add_handle($mh, $ch3);

    // éxécute toutes les requêtes simultanément
    $running = null;
    do {
      curl_multi_exec($mh, $running);
    } while ($running);

    $this->toptoday = $this->sortArray(json_decode(curl_multi_getcontent($ch1)));
    $this->topweek = $this->sortArray(json_decode(curl_multi_getcontent($ch2)));
    $this->topmonth = $this->sortArray(json_decode(curl_multi_getcontent($ch3)));
  }


  /**
   * Récupère les détails
   * GET /torrents/details/{id}
   */
  public function getDetails() {
    $ch = curl_init(self::API_URL . '/torrents/details/' . $this->id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    $result = curl_exec($ch);
    $this->details = json_decode($result);
    if(!empty($this->details->name)) {
      $this->getMoreDetails();
    }
  }

  public function getMoreDetails() {
    // construit les requêtes individuellement, sans les éxécuter
    $ch1 = curl_init(self::API_URL . '/torrents/search/"' . urlencode($this->details->name) . '"');
    $ch2 = curl_init(self::WEB_URL . '/torrents?id=' . $this->id);
    $ch3 = curl_init(self::WEB_URL . '/torrents/nfo?id=' . $this->id);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36');
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);

    $mh = curl_multi_init();
    curl_multi_add_handle($mh, $ch1);
    curl_multi_add_handle($mh, $ch2);
    curl_multi_add_handle($mh, $ch3);

    // éxécute toutes les requêtes simultanément
    $running = null;
    do {
      curl_multi_exec($mh, $running);
    } while ($running);

    $this->search = json_decode(curl_multi_getcontent($ch1));
    $this->web = curl_multi_getcontent($ch2);
    $this->nfo = curl_multi_getcontent($ch3);
    $this->nfo = $this->scrape($this->nfo, '<pre>','</pre>');
    $this->loadHash();
    $this->loadComments();
  }

  public function evalseries($input, $hdonly = false) {
    $a = $s = $size = $torrents = [];

    foreach ($input as $key => $value) {
      for ($i = 1; $i <= 24; $i++) {
        $i = $i < 10 ? 0 . $i : $i;
          stripos($value->name, 'S'.$this->saison.'E'.$i) !== false && $value->size < 3000000000 ? $a[$key]=(array)$value AND $a[$key]['episode']=$i : null;
      }
    }

    foreach ($a as $key => $value) {
      !array_keys(array_column($torrents, 'episode'), $value['episode']) && (stripos($this->cleanTitle($value['name']), $this->cleanTitle(urldecode($this->serie)) . '.S' . $this->saison) !== false) && (stripos($value['name'], '1080') !== false) ? $torrents[$value['episode']] = $value : null;
    }

    foreach ($a as $key => $value) {
      !array_keys(array_column($torrents, 'episode'), $value['episode']) && (stripos($this->cleanTitle($value['name']), $this->cleanTitle(urldecode($this->serie)) . '.S' . $this->saison) !== false) && (stripos($value['name'], '720') !== false) ? $torrents[$value['episode']] = $value : null;
    }

    if(!$hdonly){
      foreach ($a as $key => $value) {
        !array_keys(array_column($torrents, 'episode'), $value['episode']) ? $torrents[$value['episode']] = $value AND $torrents[$value['episode']]['fallback'] = 'true' : null;
      }
    }
  return $torrents;
  }

  /**
   * Requête vers l'API pour récupérer le contenu d'un torrent
   * Le torrent est retourné à l'état brut.
   * Il est ensuite encodé en base64 pour être envoyé à transmission
   * Ou écrit dans un fichier pour être téléchargé depuis le navigateur.
   * GET /torrents/download/{id}
   */
  public function getBase64Torrent() {
    $ch = curl_init(self::API_URL . '/torrents/download/' . $this->id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $this->token]);
    $result = curl_exec($ch);
    $this->base64 = base64_encode($result);
  }
}

?>
