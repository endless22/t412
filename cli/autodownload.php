<?php
require_once __DIR__ . '/../' . 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
require_once __DIR__ . '/../' . 'utils.class.php';
$t411 = new Utils(false);
$t411->order = 'size';
$mail = new PHPMailer;

class Mailing extends Utils {
  private $cr;
  private $users;

  public function __construct() {
    $this->cr = php_sapi_name() == 'cli' ? "\n" : "<br>";
    $this->users = $this->getLogins();
    $this->getUsers();
    $this->getSeriz();
  }

  /**
   * On ne garde que les utilisateurs
   * qui ont quelque chose à télécharger
   */
  public function getUsers(){
    foreach ($this->users as $key => $value) {
      if($this->isNotSelectable($value->uid)) { unset($this->users[$key]); }
    }
  }

  public function getSeriz() {
    global $mail;
    foreach ($this->users as $login) {
      /* user */
      $this->token = null;
      $this->uid = null;
      /* torrents */
      $this->requete = array();
      $this->series = array();
      $this->resultListe = array();
      $this->sorted = array();
      $this->torrents = array();
      $this->downloaded = array();
      /* mail */
      $this->message = null;
      $this->altmessage = null;

      $this->CliAuth($this->dechiffrer($login->t411user), $this->dechiffrer($login->t411pass));

      if(!isset($this->token)) { break; }
      echo '-- user -> ' . $this->dechiffrer($login->t411user) . $this->cr;
      echo '---- token -> ' . $this->token . $this->cr;
      $this->series = $this->getSeries();

      foreach ($this->series as $key => $value) {
        echo '------ série ' . $key . ' -> ' . $value->name . ' (saison ' . $value->saison . ' - épisode ' . $value->current . ')' . $this->cr;
        $this->a[$key] = $this->torrents[$key] = [];
        $this->query = $value->name;
        $this->querystring = '?limit=5000&cid=433&term[51][]=' . $value->language . '&term[45][]=' . (967+$value->saison);
        $this->torrentSearch();
        $this->requete[] = $this->search;
      }

      $this->requete = json_decode(json_encode($this->requete), true);

      foreach ($this->series as $key => $value) {
        foreach ($this->requete[$key] as $cle => $valeur) {
          for ($i = $value->current; $i <= $value->last; $i++) {
            $i = $i < 10 ? 0 . $i : $i;
            stripos($valeur['name'], 'S'.sprintf('%02d',$value->saison).'E'.$i) !== false && $valeur['size'] < 5000000000 ? $this->resultListe[$key][$cle] = $valeur AND $this->resultListe[$key][$cle]['episode']=$i : null;
          }
        }
      }

      foreach ($this->series as $key => $value) {
        if(empty($this->resultListe[$key])) { unset($this->resultList[$key]); unset($this->series[$key]); unset($this->torrents[$key]); break; };
      }

      foreach ($this->series as $key => $value) {
        foreach ($this->resultListe[$key] as $cle => $valeur) {
          !array_keys(array_column($this->torrents[$key], 'episode'), $valeur['episode']) && $valeur['episode'] >= $value->current && (stripos($this->cleanTitle($valeur['name']), $this->cleanTitle(urldecode($value->name)) . '.S' . sprintf('%02d', $value->saison)) !== false) && (stripos($valeur['name'], '1080') !== false) ? $this->torrents[$key][] = $valeur : null;
        }
      }

      foreach ($this->series as $key => $value) {
        foreach ($this->resultListe[$key] as $cle => $valeur) {
          !array_keys(array_column($this->torrents[$key], 'episode'), $valeur['episode']) && $valeur['episode'] >= $value->current && (stripos($this->cleanTitle($valeur['name']), $this->cleanTitle(urldecode($value->name)) . '.S' . sprintf('%02d', $value->saison)) !== false) && (stripos($valeur['name'], '720') !== false) ? $this->torrents[$key][] = $valeur : null;
        }
      }

      foreach ($this->series as $key => $value) {
        array_multisort(array_column($this->torrents[$key], 'episode'), $this->torrents[$key]);
      }

      if(!empty($this->torrents)) {
        foreach ($this->series as $key => $value) {
          foreach ($this->torrents[$key] as $cle => $valeur) {
            $this->id = $valeur['id'];
            if($value->current != $value->last && ($value->current != $valeur['episode'] || $value->current == 1) && $this->addTorrent($valeur['id'])){
              $this->updateSerie($value->id, $valeur['episode']);
              $this->downloaded[] = $valeur;
              echo '-------- yeaaaaah ajouté ' . $valeur['name'] . $this->cr;
            } else {
              echo '-------- hmmmmmmmmm ' . $valeur['name'] . $this->cr;
            }
          }
        }
      }

      if(!empty($this->downloaded)) {

        $pluriel = count($this->downloaded) > 1 ? 's' : null;
        $this->message = 'Le script vient de lancer automatiquement le téléchargement de <b>' . count($this->downloaded) . '</b> torrent' . $pluriel . '.<br>'
          . 'Liste des fichiers téléchargés:<br>'
          . '<ol>';
        $this->altmessage = 'Le script vient de lancer automatiquement le téléchargement de ' . count($this->downloaded) . ' torrents.'
          . 'Liste des fichiers téléchargés:';

        foreach ($this->downloaded as $key => $value) {
        $this->message .= '<li><a href="https://' . $this->domaineName . '/details/' . $value['id'] . '">' . $value['name'] . '</a> (' . $this->formatBytes($value['size']) . ')</li>';
        $this->altmessage .= '  . ' . $value['name'] . ' (' . $this->formatBytes($value['size']) . ')';
        }

        $this->message .= '</ol>';

        //$mail->SMTPDebug = 4;
        $mail->CharSet = 'UTF-8';

        $mail->isSMTP();
        $mail->Host = $this->domainName;
        $mail->SMTPAuth = true;
        $mail->Username = 'john@doe.com';
        $mail->Password = 'changeme';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->SMTPOptions = array(
          'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false
          )
        );

        $mail->setFrom('noreply@' . $this->domainName, 'Alerte Torrent');
        $mail->addAddress($this->dechiffrer($login->email), $this->dechiffrer($login->t411user));
        $mail->addReplyTo('noreply@' . $this->domainName, 'Alerte Torrent');
        $mail->isHTML(true);

        $mail->Subject = count($this->downloaded) . ' torrent' . $pluriel . ' téléchargé' . $pluriel;
        $mail->Body = $this->message;
        $mail->AltBody = $this->altmessage;

        if(!$mail->send()) {
          echo 'Message could not be sent.';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
          echo '---------- Le message a été envoyé.' . $this->cr;
        }
      }
    }
  }
}

new Mailing;
?>
