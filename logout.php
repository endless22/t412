<?php

/**
 * Détruit les cookies et redirige
 * vers la page d'accueil
 */
setcookie('token', '', 1);
setcookie('username', '', 1);
setcookie('downloaded', '', 1);
setcookie('uploaded', '', 1);

// bye bye
header('Location: /login/');
exit;

?>
