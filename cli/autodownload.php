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
    $this->users = $this->getCredentials();
    $this->getSeriz();
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

      $this->CliAuth($this->decrypt($login->t411username), $this->decrypt($login->t411password));

      if (!isset($this->token)) { break; }
      echo '-- user -> ' . $this->decrypt($login->t411username) . $this->cr;
      echo '---- token -> ' . $this->token . $this->cr;
      $this->series = $this->getSeries();

      foreach ($this->series as $key => $value) {
        echo '------ série ' . $key . ' -> ' . $value->name . ' (saison ' . $value->season . ' - épisode ' . $value->current . ')' . $this->cr;
        $this->torrents[$key] = array();
        $this->query = $value->name;
        $this->querystring = '?limit=5000&cid=433&term[51][]=' . $value->language . '&term[45][]=' . (967+$value->season);
        $this->torrentSearch();
        $this->requete[] = $this->search;
      }

      foreach ($this->series as $key => $value) {
        if (empty($this->requete[$key]) || $value->current == $value->last) {
          unset($this->requete[$key], $this->series[$key], $this->torrents[$key]);
        }
      }

      $this->requete = array_values($this->requete);
      $this->series = array_values($this->series);
      $this->torrents = array_values($this->torrents);

      foreach ($this->series as $key => $value) {
        $this->torrents[$key][] = $this->evalseries($value->name, $this->requete[$key], sprintf('%02d', $value->season));
      }

      foreach ($this->series as $key => $value) {
        array_multisort(array_column($this->torrents[$key][$key], 'episode'), $this->torrents[$key][$key]);
      }

      if(!empty($this->torrents)) {
        foreach ($this->series as $key => $value) {
          foreach ($this->torrents[$key][$key] as $cle => $valeur) {
            if($value->current != $value->last && ($value->current != $valeur['episode'] || $value->current == 1)) {
              try {
                $this->addTorrent('torrent', (string)$valeur['id'], (string)$value->server);
                $this->updateSerie($value->id, $valeur['episode']);
                $this->downloaded[] = $valeur;
                echo '-------- yeaaaaah ajouté ' . $valeur['name'] . $this->cr;
              } catch (Exception $e) {
                echo 'fail -> ' . $e->getMessage() . $this->cr;
              }
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
        $this->message .= '<li><a href="https://' . 'domain.tld' . '/details/' . $value['id'] . '">' . $value['name'] . '</a> (' . $this->formatBytes($value['size']) . ')</li>';
        $this->altmessage .= '  . ' . $value['name'] . ' (' . $this->formatBytes($value['size']) . ')';
        }

        $this->message .= '</ol>';

        //$mail->SMTPDebug = 4;
        $mail->CharSet = 'UTF-8';

        $mail->isSMTP();
        $mail->Host = '';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->SMTPOptions = array(
          'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false
          )
        );

        $mail->setFrom('noreply@domain.tld', 'Alerte Torrent');
        $mail->addAddress('johndoe@domain.tld', 'Alerte Torrent');
        $mail->addReplyTo('noreply@domain.tld', 'Alerte Torrent');
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
