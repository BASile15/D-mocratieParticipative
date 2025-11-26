<?php
require_once("modele/theme.php");
require_once("modele/role.php");  

class controleurTheme {

    public static function formTheme() {
        $title = "Créer Théme";
        include("vue/debut.php");
        include("vue/formulaires/formAjouterTheme.html");
        include("vue/finSansFooter.html");
    }

    public static function afficherThemes($idGroupe) {
        $_SESSION['idGroupe']=$idGroupe;
        $title = "Thèmes du groupe";
        include("vue/debut.php");
        include("vue/menu.html");

        if (isset($_SESSION['id_utilisateur'])) {
            $idUtilisateur = $_SESSION['id_utilisateur'];
            $isAdmin = Role::isAdminInGroup($idUtilisateur, $idGroupe);
            $isMode = Role::isModeratorInGroupe($idUtilisateur, $idGroupe);
        }

        if (isset($idGroupe)) {
            $themes = Theme::getThemesByGroupe($idGroupe);

            
                include("vue/themes/afficherThemes.php");
            
        }

        include("vue/fin.html");
    }
    
    public static function ajouterTheme() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idGroupe = $_SESSION['idGroupe'];
            $budgetLimite = $_POST['budgetLimite'];
            $nomTheme = $_POST['nomTheme']; 
            $description = $_POST['description'];
            Theme::créerTheme($idGroupe, $budgetLimite, $nomTheme, $description);
        }
        header("Location: routeur.php?controleur=controleurTheme&action=afficherThemes&idE=".$_SESSION["idGroupe"]);
    }

    public static function voirTheme($idTheme) {
        $title = "Détails du thème";
        include("vue/debut.php");
        include("vue/menu.html");

        $theme = Theme::getThemeById($idTheme);

        if (!$theme) {
            echo "Le thème spécifié n'existe pas.";
        } else {
            echo "<h1>" . htmlspecialchars($theme['nomTheme']) . "</h1>";
            echo "<p>" . htmlspecialchars($theme['descriptionTheme']) . "</p>";
        }

        include("vue/fin.html");
    }


}
?>
