<?php
require_once("config/connexion.php");

class Theme {

    private $idTheme;
    private $idGroupe;
    private $nomTheme;
    private $description;
    private $budgetLimite;

    public function __construct($idTheme = null, $idGroupe = null, $nomTheme = null, $description = null, $budgetLimite = null) {
        $this->idTheme = $idTheme;
        $this->idGroupe = $idGroupe;
        $this->nomTheme = $nomTheme;
        $this->description = $description;
        $this->budgetLimite = $budgetLimite;
    }

    public static function userIsInGroup($idUtilisateur, $idGroupe) {   
        $pdo = Connexion::pdo();
        $sql = "SELECT COUNT(*) FROM appartient WHERE idUtilisateur = :idUtilisateur AND idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur, ':idGroupe' => $idGroupe]);
        $result = $stmt->fetchColumn();
        return $result > 0;
    }
    
    public static function getThemesByGroupe($idGroupe) {
        if (!isset($_SESSION['id_utilisateur'])) {
            die("Vous devez être connecté pour accéder aux thèmes.");
        }
        $idUtilisateur = $_SESSION['id_utilisateur'];
        if (!self::userIsInGroup($idUtilisateur, $idGroupe)) {
            die("Vous n'êtes pas membre de ce groupe.");
        }
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Theme WHERE idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idGroupe' => $idGroupe]);
    
        $themes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $themes[] = new Theme(
                $row['idTheme'], 
                $row['idGroupe'], 
                $row['nomTheme'], 
                $row['description'], 
                $row['budgetLimite']
            );
        }
        return $themes;
    }
    
    public function afficher() {
        echo "<section class='sectionAfficherTheme'>";
        echo "<p><a href='routeur.php?controleur=controleurProposition&action=afficherPropositions&idE=" . urlencode($this->idTheme) . "'>" . htmlspecialchars($this->nomTheme) . "</a></p>";
        echo "<p>Description : " . htmlspecialchars($this->description) . "</p>";
        if ($this->budgetLimite!=null) {
            echo "<p>Budget Limite : " . htmlspecialchars($this->budgetLimite) . "</p>";
        }else echo "<p>Budget non défini par le Décideur ! </p>";
        echo "</section>";
    }

    // Récupérer un thème par son ID
    public static function getThemeById($idTheme) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Theme WHERE idTheme = :idTheme";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idTheme' => $idTheme]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function créerTheme($idGroupe, $budgetLimite, $nomTheme, $description) {
        $pdo = Connexion::pdo();
        $sql = "INSERT INTO Theme (idTheme, idGroupe, nomTheme, budgetLimite ,description) 
                VALUES (null, :idGroupe, :nomTheme, :budgetLimite, :description)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idGroupe' => $idGroupe,
            ':nomTheme' => $nomTheme,
            ':budgetLimite' => null,
            ':description' => $description
        ]);
    }
}
?>
