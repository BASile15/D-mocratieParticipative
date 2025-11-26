<?php
require_once("config/connexion.php");
require_once("modele/utilisateur.php");
require_once("modele/groupe.php");

class Commentaire {

    private $idCommentaire;
    private $contenu;
    private $dateCommentaire;
    private $idUtilisateur;
    private $idProposition;

    // Getters
    public function getIdCommentaire() { return $this->idCommentaire; }
    public function getContenu() { return $this->contenu; }
    public function getDateCommentaire() { return $this->dateCommentaire; }
    public function getIdUtilisateur() { return $this->idUtilisateur; }
    public function getIdProposition() { return $this->idProposition; }

    // Setters
    public function setIdCommentaire($valeur) { $this->idCommentaire = $valeur; }
    public function setContenu($valeur) { $this->contenu = $valeur; }
    public function setDateCommentaire($valeur) { $this->dateCommentaire = $valeur; }
    public function setIdUtilisateur($valeur) { $this->idUtilisateur = $valeur; }
    public function setIdProposition($valeur) {$this->idProposition = $valeur;}

    // Constructeur
    public function __construct($idCommentaire, $contenu, $dateCommentaire, $idUtilisateur, $idProposition) {
        $this->idCommentaire = $idCommentaire;
        $this->contenu = $contenu;
        $this->dateCommentaire = $dateCommentaire;
        $this->idUtilisateur = $idUtilisateur;
        $this->idProposition = $idProposition;
    }

    public static function nouveauCommentaire($contenu, $idUtilisateur, $idProposition) {
        $pdo = Connexion::pdo();
        $sql = "INSERT INTO Commentaire (contenu, dateCommentaire, idUtilisateur, idProposition)
                VALUES (:contenu, NOW(), :idUtilisateur, :idProposition)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':contenu' => $contenu,
            ':idUtilisateur' => $idUtilisateur,
            ':idProposition' => $idProposition
        ]);
    }

    // Méthode pour récupérer tous les commentaires d'une proposition
    public static function getCommentairesParProposition($idProposition) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Commentaire WHERE idProposition = :idProposition ORDER BY dateCommentaire DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProposition' => $idProposition]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, "Commentaire");
        return $stmt->fetchAll();
    }
}
?>
