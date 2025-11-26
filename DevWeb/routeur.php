<?php
require_once("config/connexion.php");
Connexion::connect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controleur = "controleurGroupe";
$action = "lireGroupesUtilisateur";

if (!isset($_SESSION['id_utilisateur'])) {
    if (!isset($_GET['action']) || !in_array($_GET['action'], ["formConnexion", "connexion", "formInscription", "inscription","deconnecterUtilisateurWErr","formConnexionWERR"])) {
                if (isset($_GET['action']) && strpos($_GET['action'], 'WERR') !== false) {
            require_once("controleur/controleurUtilisateur.php");
            header("Location: routeur.php?controleur=controleurUtilisateur&action=formConnexionWERR");
            exit;
        }
        require_once("controleur/controleurUtilisateur.php");
        header("Location: routeur.php?controleur=controleurUtilisateur&action=formConnexion");
        exit; 
    }
}


$tableauControleurs = [
    "controleurUtilisateur",    
    "controleurGroupe", 
    "controleurTheme", 
    "controleurProposition", 
    "controleurSignalement", 
    "controleurInvitation",
    "controleurRole",
    "controleurVote"
];
$actionsAutorisees = [
    "controleurUtilisateur" => ["formConnexion", "connexion", "formInscription", "inscription", "formCreerGroupe", "Creer Groupe", "MonProfil", "formModifierInfoUtilisateur", "modifierProfil", "deconnecterUtilisateur","deconnecterUtilisateurWErr"],
    "controleurGroupe" => ["lireGroupesUtilisateur", "CreerGroupe", "lireUtilisateursDuGroupe","expulserUtilisateur"],
    "controleurTheme" => ["afficherThemes", "voirTheme", "afficherPropositions","formTheme","ajouterTheme"],
    "controleurProposition" => ["afficherPropositions", "PropositionParId","formProposition","créerProposition"],
    "controleurSignalement" => ["afficherSignalements", "creerSignalementCommentaire", "signalerProposition", "supprimerSignalement", "supprimerSignalementProposition", "afficherPSignalement"],
   "controleurInvitation"=> ["LireInvitations","AfficherFormulaireInvitation", "NouvelleInvitation", "AccepterInvitation", "RefuserInvitation"],
    "controleurRole" => ["changerRoleUtilisateur"],
    "controleurVote"=>["Voter","afficherPageVote","afficherResultatsVote","lancerVote"]
];

if (isset($_GET['controleur']) && in_array($_GET['controleur'], $tableauControleurs)) {
    $controleur = $_GET['controleur'];
}

$action = isset($_GET['action']) && in_array($_GET['action'], $actionsAutorisees[$controleur] ?? []) 
    ? $_GET['action'] 
    : "lireGroupesUtilisateur";
echo "<script>console.log('Controleur: " . $controleur . " Action: " . $action . "');</script>";

require_once("controleur/$controleur.php");
if (isset($_GET['idE'])) {
    $controleur::$action($_GET["idE"]);
} else {
    $controleur::$action();
}
?>
