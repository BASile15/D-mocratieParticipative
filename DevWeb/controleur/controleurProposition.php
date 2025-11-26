<?php
require_once("modele/proposition.php");

class controleurProposition {

    public static function formProposition() {
        $title = "Créer une Proposition";
        include("vue/debut.php");
        include("vue/formulaires/formAjouterProposition.html");
        include("vue/finSansFooter.html");
    }

    public static function afficherPropositions($idTheme) {
        $_SESSION["idTheme"]=$idTheme;
        $title = "Proposition ";
        include("vue/debut.php");
        include("vue/menu.html");
        if(isset($idTheme)){
            $propositions = Proposition::getPropositionsByTheme($idTheme);
            if(!$propositions){
                require("vue/proposition/aucuneProposition.php");
            }else require("vue/proposition/afficherPropositions.php");
        }
        include("vue/fin.html");
    }

    
    public static function créerProposition() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'];
            $cout = $_POST['cout'];
            $description = $_POST['description']; 
            $dureeDiscussion = $_POST['dureeDiscussion'];
            Proposition::créerProposition($titre, $cout, $description, $dureeDiscussion);
        }
        header("Location: routeur.php?controleur=controleurProposition&action=afficherPropositions&idE=".$_SESSION["idTheme"]);
    }

    public static function PropositionParId($idProposition) {
        $title = "Proposition Signaler";
        include("vue/debut.php");
        include("vue/menu.html");
        if(isset($idProposition)){
            $propositions = Proposition::getPropositionById($idProposition);
            if(!$propositions){
                require("vue/proposition/aucuneProposition.php");
            }else require("vue/proposition/affichagePropositionSignaler.php");
        }
        include("vue/fin.html");
    }
}

?>
