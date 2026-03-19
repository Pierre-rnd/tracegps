<?php
// Projet TraceGPS - version web mobile
// fichier : controleurs/CtrlMenu.php

// on vérifie si le demandeur de cette action est bien authentifié
if ( $_SESSION['niveauConnexion'] == 0) {
    header ("Location: index.php?action=Deconnecter");
}
else {
    // ajout de la vérification du mot de passe
    include_once ('modele/Outils.php');

    $mdp = $_SESSION['mdp'];   // mot de passe utilisé lors de la connexion

    if ( ! Outils::estUnMdpValide($mdp) ) {

        // préparation des données pour la vue
        $nouveauMdp = '';
        $confirmationMdp = '';
        $afficherMdp = 'off';

        $message = "Pour des raisons de sécurité, nous vous invitons à changer votre mot de passe. 
Le nouveau mot de passe doit comporter au moins 8 caractères, dont au moins une lettre 
minuscule, une lettre majuscule et un chiffre !";

        $typeMessage = 'avertissement';
        $themeFooter = $themeProbleme;

        // affichage de la vue de changement de mot de passe
        include_once ('vues/VueChangerDeMdp.php');
        exit;
    }

    // connexion du serveur web à la base MySQL
    include_once ('modele/DAO.php');
    $dao = new DAO();

    // affichage normal du menu
    include_once ('vues/VueMenu.php');
}