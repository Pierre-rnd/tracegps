<?php
// Projet TraceGPS - services web
// fichier :  api/services/CreerUnUtilisateur.php
// Dernière mise à jour : 3/7/2021 par dP

// Rôle : ce service permet à un utilisateur de se créer un compte
// Le service web doit recevoir 4 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     adrMail : son adresse mail
//     numTel : son numéro de téléphone
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// Les paramètres doivent être passés par la méthode GET :
//     http://<hébergeur>/tracegps/api/CreerUnUtilisateur?pseudo=turlututu&adrMail=delasalle.sio.eleves@gmail.com&numTel=1122334455&lang=xml

// connexion du serveur web à la base MySQL
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];
$idTrace = ( empty($this->request['idTrace'])) ? "" : $this->request['idTrace'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    // Les paramètres doivent être présents
    if ( $pseudo == "" || $mdpSha1 == "" )
    {	$msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }else {
        		if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 ) {
        			$msg = "Erreur : authentification incorrecte.";
        			$code_reponse = 401;
        		}
            
            else{
                $laTrace=$dao->getUneTrace($idTrace);
                if($laTrace == null) {
                    $msg ="Erreur : parcours inexistant.";
                    $code_reponse = 404;
                }
                    else{
                        $utilisateur = $dao->getUnUtilisateur($pseudo);
                        $id = $utilisateur->getId();

                        $Proprietaire = $laTrace->getIdUtilisateur();
                        $autorise = $dao->autoriseAConsulter($Proprietaire,$id);

                        if($id != $Proprietaire && !$autorise) {
                            $msg = "Erreur : vous n'êtes pas autorisé par le propriétaire du parcours.";
                            $code_reponse = 401;
                            exit;

                        }
                        else{
                            $laTrace = $dao->getUneTrace( $idTrace );
                            $msg = "Données de la trace demandée.";
                            $code_reponse = 200;
                            $LesPoints = $dao->getLesPointsDeTrace($idTrace) ;
                            
                        }
                    }
            }
        }
}
 
// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML ($msg, $laTrace, $LesPoints);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON ($msg, $laTrace, $LesPoints);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================

// création du flux XML en sortie
function creerFluxXML($msg, $laTrace, $LesPoints)
{	
    /* Exemple de code XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!--Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes-->
        <data>
            <reponse>Erreur : authentification incorrecte.</reponse>
        </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();
	
	// specifie la version et le type d'encodage
	$doc->version = '1.0';
	$doc->encoding = 'UTF-8';
	
	// crée un commentaire et l'encode en UTF-8
	$elt_commentaire = $doc->createComment('Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes');
	// place ce commentaire à la racine du document XML
	$doc->appendChild($elt_commentaire);
	
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);

    // traitement des utilisateurs
	
	    // place l'élément 'donnees' dans l'élément 'data'
	    $elt_donnees = $doc->createElement('donnees');
	    $elt_data->appendChild($elt_donnees);
	    
	    $elt_laTrace = $doc->createElement('trace');
	    $elt_donnees->appendChild($elt_laTrace);
	
	    foreach ($laTrace as $trace)
		{
		    $elt_id = $doc->createElement('id', $trace->getId());
		    $elt_laTrace->appendChild($elt_id);
		    
		    $elt_dateHeureDebut     = $doc->createElement('dateHeureDebut', $trace->getDateHeureDebut());
		    $elt_laTrace->appendChild($elt_dateHeureDebut);
		    
		    $elt_terminee    = $doc->createElement('terminee', $trace->getTerminee());
		    $elt_laTrace->appendChild($elt_terminee);
		    
		    $elt_dateHeureFin    = $doc->createElement('dateHeureFin', $trace->getDateHeureFin());
		    $elt_laTrace->appendChild($elt_dateHeureFin);
		    
            $elt_idUtilisateur = $doc->createElement('idUtilisateur', $trace->getIdUtilisateur());
		    $elt_laTrace->appendChild($elt_idUtilisateur);
		}

        $elt_lesPoints = $doc->createElement('LesPoints');
	    $elt_donnees->appendChild($elt_lesPoints);

        foreach($LesPoints as $Points)
        {
            $elt_Points = $doc->createElement('point');	    
		    $elt_lesPoints->appendChild($elt_Points);

            $elt_id         = $doc->createElement('id', $Points->getId());
		    $elt_Points->appendChild($elt_id);
		    
		    $elt_latitude     = $doc->createElement('latitude', $Points->getLatitude());
		    $elt_Points->appendChild($elt_latitude);
		    
		    $elt_longitude   = $doc->createElement('longitude', $Points->getLongitude());
		    $elt_Points->appendChild($elt_longitude);
		    
		    $elt_altitude    = $doc->createElement('altitude', $Points->getAltitude());
		    $elt_Points->appendChild($elt_altitude);
		    
            $elt_dateHeure = $doc->createElement('dateHeure', $Points->getDateHeure());
		    $elt_Points->appendChild($elt_dateHeure);

            $elt_rythmeCardio = $doc->createElement('rythmeCardio', $Points->getRythmeCardio());
		    $elt_Points->appendChild($elt_rythmeCardio);
        }
	// Mise en forme finale
	$doc->formatOutput = true;
	
	// renvoie le contenu XML
	return $doc->saveXML();
}

// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg, $laTrace, $LesPoints)
{
    /* Exemple de code JSON
         {
             "data": {
                "reponse": "Erreur : authentification incorrecte."
             }
         }
     */
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    
    $lesTraces = array();
        
            
	foreach ($laTrace as $trace)
		{
             $uneTrace = array();

		    $uneTrace["id"] = $trace->getId();
            $uneTrace["dateHeureDebut"] = $trace->getDateHeureDebut();
            $uneTrace["terminee"] = $trace->getTerminee();
            $uneTrace["dateHeureFin"] = $trace->getDateHeureFin();
            $uneTrace["idUtilisateur"] = $trace->getIdUtilisateur();
            
            $lesTraces[] = $uneTrace;

		}

        $elt_laTrace = ["trace" => $lesTraces];
        $elt_data = ["reponse" => $msg, "donnees" => $elt_laTrace];

        $plsPoints = array();

        foreach ($LesPoints as $Points)
		{
             $unPoints = array();

		    $unPoints["id"] = $Points->getId();
            $unPoints["latitude"] = $Points->getLatitude();
            $unPoints["longitude"] = $Points->getLongitude();
            $unPoints["altitude"] = $Points->getAltitude();
            $unPoints["dateHeure"] = $Points->getDateHeure();
            $unPoints["rythmeCardio"] = $Points->getRythmeCardio();
            
            $LesPoints[] = $unPoints;

		}
    $elt_lesPoints = ["lesPoints" => $plsPoints];
    $elt_data += ["reponse" => $msg, "donnees" => $elt_lesPoints];

    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>
