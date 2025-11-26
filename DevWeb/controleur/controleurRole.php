<?php
require_once("modele/role.php");

class controleurRole {
    public static function changerRoleUtilisateur() {
        $urlPrecedente = $_SERVER['HTTP_REFERER'] ?? 'routeur.php?controleur=controleurGroupe&action=lireUtilisateursDuGroupe';
        $resultat = Role::changerRoleUser($_POST["idUtilisateur"], $_POST["idGroupe"], $_POST["idRole"]);
        
        if (!$resultat) {
            echo "<script>alert('Échec de la modification du rôle.');</script>";
        } else {
            echo "<script>alert('Rôle modifié avec succès.');</script>";
        }
        header("Location: $urlPrecedente");
        exit;
    }
}
?>
