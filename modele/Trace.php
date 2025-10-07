<?php
use modele\Point;
// Projet TraceGPS
// fichier : modele/Trace.php
// Rôle : la classe Trace représente une trace ou un parcours
// Dernière mise à jour : 9/7/2021 par dP
include_once ('PointDeTrace.php');
class Trace
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $id; // identifiant de la trace
    private $dateHeureDebut; // date et heure de début
    private $dateHeureFin; // date et heure de fin
    private $terminee; // true si la trace est terminée, false sinon
    private $idUtilisateur; // identifiant de l'utilisateur ayant créé la trace
    private $lesPointsDeTrace; // la collection (array) des objets PointDeTrace formant la trace
    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function __construct($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur) {
        $this->id = $unId;
        $this->dateHeureDebut = $uneDateHeureDebut;
        $this->dateHeureFin = $uneDateHeureFin;
        $this->terminee = $terminee;
        $this->idUtilisateur = $unIdUtilisateur;
        $this->lesPointsDeTrace = array();
    }
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function getId() {return $this->id;}
    public function setId($unId) {$this->id = $unId;}
    
    public function getDateHeureDebut() {return $this->dateHeureDebut;}
    public function setDateHeureDebut($uneDateHeureDebut) {$this->dateHeureDebut = $uneDateHeureDebut;}
    public function getDateHeureFin() {return $this->dateHeureFin;}
    public function setDateHeureFin($uneDateHeureFin) {$this->dateHeureFin= $uneDateHeureFin;}
    
    public function getTerminee() {return $this->terminee;}
    public function setTerminee($terminee) {$this->terminee = $terminee;}
    
    public function getIdUtilisateur() {return $this->idUtilisateur;}
    public function setIdUtilisateur($unIdUtilisateur) {$this->idUtilisateur = $unIdUtilisateur;}
    public function getLesPointsDeTrace() {return $this->lesPointsDeTrace;}
    public function setLesPointsDeTrace($lesPointsDeTrace) {$this->lesPointsDeTrace = $lesPointsDeTrace;}
    // Fournit une chaine contenant toutes les données de l'objet
    public function toString() {
    $msg = "Id : " . $this->getId() . "<br>";
    $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
    if ($this->getDateHeureDebut() != null) {
    $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
    }
    if ($this->getTerminee()) {
    $msg .= "Terminée : Oui <br>"; 
    }
    else {
    $msg .= "Terminée : Non <br>";
    }
    $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
    if ($this->getNombrePoints() > 0) { 
    if ($this->getDateHeureFin() != null) {
    $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
    }
    $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
    $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
    $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
    $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
    $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
    $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
    $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
    $msg .= "Centre du parcours : " . "<br>";
    $msg .= " - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
    $msg .= " - Longitude : " . $this->getCentre()->getLongitude() . "<br>";
    $msg .= " - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
    }
    return $msg;
    }

    public function getNombrePoints(){
        return sizeof($this->lesPointsDeTrace);
    }

    public function getCentre(){
        $centrePoint = new Point(0,0,0);
        $premierPoint = $this->lesPointsDeTrace[0];
        $latMax = $premierPoint->getLatitude();
        $latMin = $premierPoint->getLatitude();
        $longMax = $premierPoint->getLongitude(); 
        $longMin = $premierPoint->getLongitude();

        for ($i = 0; $i < sizeof($this->lesPointsDeTrace);$i ++)
        {
            $lePoint = $this->lesPointsDeTrace[$i];
            if ($latMax < $lePoint->getLatitude())
            {
                $latMax = $lePoint->getLatitude();
            }
            if ($latMin > $lePoint->getLatitude())
            {
                $latMin = $lePoint->getLatitude();
            }
            if ($longMax < $lePoint->getLongitude())
            {
                $longMax = $lePoint->getLongitude();
            }
            if ($longMin > $lePoint->getLongitude())
            {
                $longMin = $lePoint->getLongitude();
            }
        }
        $centrePoint->setLatitude(($latMax + $latMin) /2);
        $centrePoint->setLongitude(($longMax + $longMin) /2);
        return $centrePoint;
    }

    public function getDenivele(){
        $premierPoint = $this->lesPointsDeTrace[0];
        $AltMax = $premierPoint->getAltitude(); 
        $AltMin = $premierPoint->getAltitude();
        for ($i = 0; $i < sizeof($this->lesPointsDeTrace);$i ++)
        {
            $lePoint = $this->lesPointsDeTrace[$i];

            if ($AltMax < $lePoint->getAltitude())
            {
                $AltMax = $lePoint->getAltitude();
            }
            if ($AltMin > $lePoint->getAltitude())
            {
                $AltMin = $lePoint->getAltitude();
            }
        }
        return round($AltMax - $AltMin, 2);
    }

    public function getDureeEnSecondes()
    {
        if (count($this->lesPointsDeTrace) == 0)
        {
            return 0;
        }
        $premierPoint = $this->lesPointsDeTrace[0];
        $dernierPoint = end($this->lesPointsDeTrace);
        return strtotime($dernierPoint->getDateHeure()) - strtotime($premierPoint->getDateHeure());
    }

    public function getDureeTotale()
    {
        $dureeSecondes = $this->getDureeEnSecondes();

        $heures = $dureeSecondes / 3600;
        $minutes = ($dureeSecondes % 3600) /60;
        $secondes = $dureeSecondes % 60;

        return sprintf("%02d",$heures) . ":" . sprintf("%02d",$minutes) . ":" . sprintf("%02d",$secondes);
    }

    public function getDistanceTotale()
    {
        if (count($this->lesPointsDeTrace) == 0)
        {
            return 0;
        }
        $dernierPoint = end($this->lesPointsDeTrace);
        return $dernierPoint->getDistanceCumulee();
    }

    public function getDenivelePositif()
    {
        $denivelePos = 0;

        for ($i = 0; $i < sizeof($this->lesPointsDeTrace)-1;$i ++)
        {
            $pointActuel = $this->lesPointsDeTrace[$i];
            $pointSuivant = $this->lesPointsDeTrace[$i + 1];

            $diffAltitude = $pointSuivant->getAltitude() - $pointActuel->getAltitude();
            if ($diffAltitude > 0)
            {
                $denivelePos += $diffAltitude;
            }
        }
        return round($denivelePos,2);
    }

    public function getDeniveleNegatif()
    {
        $deniveleNeg = 0;

        for ($i = 0; $i < sizeof($this->lesPointsDeTrace)-1;$i ++)
        {
            $pointActuel = $this->lesPointsDeTrace[$i];
            $pointSuivant = $this->lesPointsDeTrace[$i + 1];

            $diffAltitude = $pointSuivant->getAltitude() - $pointActuel->getAltitude();
            if ($diffAltitude < 0)
            {
                $deniveleNeg -= $diffAltitude;
            }
        }
        return round($deniveleNeg,2);
    }

    public function getVitesseMoyenne()
    {
        $dureeSec = $this->getDureeEnSecondes();
        if ($dureeSec == 0) return 0;
        return $this->getDistanceTotale() / ($dureeSec / 3600);
    }

    public function ajouterPoint(PointDeTrace $nouveauPoint)
    {
        if (count($this->lesPointsDeTrace) == 0)
        {
            $nouveauPoint->setDistanceCumulee(0);
            $nouveauPoint->setTempsCumule(0);
            $nouveauPoint->setVitesse(0);
        }
        else{
            $dernierPoint = end($this->lesPointsDeTrace);
            $distance = Point::getDistance($nouveauPoint,$dernierPoint);
            $tempsSec = strtotime($nouveauPoint->getDateHeure()) - strtotime($dernierPoint->getDateHeure());

            $nouveauPoint->setDistanceCumulee($dernierPoint->getDistanceCumulee()+$distance);
            $nouveauPoint->setTempsCumule($dernierPoint->getTempsCumule() + $tempsSec);

            if ($tempsSec > 0)
            {
                $nouveauPoint->setVitesse(($distance / $tempsSec) * 3600);
            }
            else
            {
                $nouveauPoint->setVitesse(0);
            }
        }
        $this->lesPointsDeTrace[] = $nouveauPoint;
    }
    public function viderListePoints()
    {
        $this->lesPointsDeTrace = array();
    }

}