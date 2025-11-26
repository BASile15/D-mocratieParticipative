<?php
require_once("modele/signalement.php");
require_once("modele/proposition.php");
class controleurSignalement {

    public static function afficherSignalements($idGroupe) {
        $title = "Signalements du groupe";
        include("vue/debut.php");
        include("vue/menu.html");
        $signalements = Signalement::getSignalementsParGroupe($idGroupe);
        include("vue/signalement/afficherSignalement.php");
        include("vue/fin.html");
    }

    public static function creerSignalementCommentaire($idUtilisateur, $idCommentaire, $raison) {
        Signalement::creerSignalementCommentaire($idUtilisateur, $idCommentaire, $raison);
        echo "<p>Le signalement du commentaire a été créé avec succès.</p>";
    }
    

    public static function signalerProposition() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idProposition']) && isset($_POST['raison'])) {
            $idProposition = $_POST['idProposition'];
            $idUtilisateur = $_SESSION['id_utilisateur'];
            $raison = htmlspecialchars($_POST['raison']);
            Signalement::creerSignalementProposition($idUtilisateur, $idProposition, $raison);
            echo "<p>Le signalement de la proposition a été créé avec succès.</p>";
            exit; 
        } else {
            echo "Formulaire non soumis correctement.";
        }
    }
    
    
    

    // Supprimer un signalement
    public static function supprimerSignalementProposition($idProposition) {
        Signalement::supprimerSignalementProposition($idProposition);
        Proposition::supprimerProposition($idProposition);
        echo "<p>Le signalement a été supprimé.</p>";
        header("Location: routeur.php?controleur=controleurSignalement&action=afficherSignalements&idE=" . $_SESSION['idGroupe']);
    }
    public static function supprimerSignalement($idProposition){
        Signalement::supprimerSignalementProposition($idProposition);
        echo "<p>Le signalement a été supprimé.</p>";
        header("Location: routeur.php?controleur=controleurSignalement&action=afficherSignalements&idE=" . $_SESSION['idGroupe']);
    }
    

    // Vérifier si l'utilisateur est admin pour le groupe
    private static function estAdmin($idUtilisateur, $idGroupe) {
        $pdo = Connexion::pdo();
        $sql = "SELECT idRole FROM appartient WHERE idUtilisateur = :idUtilisateur AND idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur, ':idGroupe' => $idGroupe]);
        $role = $stmt->fetchColumn();
        return in_array($role, [2, 3]); 
    }

    public static function afficherPSignalement($idProposition){
        $title = "Proposition signaler";
        include("vue/debut.php");
        include("vue/menu.html");
        $proposition=Proposition::getPropositionById($idProposition);
        include("vue/signalement/afficherUnSignalement.php");
        include("vue/fin.html");
    }
}
?>

