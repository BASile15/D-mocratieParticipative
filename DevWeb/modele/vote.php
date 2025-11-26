<?php
require_once("config/connexion.php");

class Vote {

    private $idVote;
    private $idProposition;
    private $idUtilisateur;
    private $choix;
    private $dateVote;

    public function __construct($idVote, $idProposition, $idUtilisateur, $choix, $dateVote) {
        $this->idVote = $idVote;
        $this->idProposition = $idProposition;
        $this->idUtilisateur = $idUtilisateur;
        $this->choix = $choix;
        $this->dateVote = $dateVote;
    }
    
    public static function creerVote($idProposition, $choix) {
        $pdo = Connexion::pdo();
        $sql = "INSERT INTO Vote (idVote, idUtilisateur, idProposition, choix, dateVote) 
                VALUES (null, :idUtilisateur, :idProposition, :choix, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idUtilisateur' => $_SESSION["id_utilisateur"],
            ':idProposition' => $idProposition,
            ':choix' => $choix
        ]);
    }
    
    public static function lancerVote($idProposition) {
        $pdo = Connexion::pdo();
        $sqlProposition = "UPDATE Proposition SET enVote = 1 WHERE idProposition = :idProposition";
        $stmt = $pdo->prepare($sqlProposition);
        $stmt->execute([
            ':idProposition' => $idProposition
        ]);
        return $stmt;
    }

    public static function getResultatsVote($idProposition) {
        $pdo = Connexion::pdo();
        $sql = "SELECT choix, COUNT(*) as count FROM Vote WHERE idProposition = :idProposition GROUP BY choix";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProposition' => $idProposition]);
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pour = 0;
        $contre = 0;
        $abstention=0;
        foreach ($resultats as $resultat) {
            if ($resultat['choix'] == 'Pour') {
                $pour = $resultat['count'];
            } elseif ($resultat['choix'] == 'Contre') {
                $contre = $resultat['count'];
            }elseif ($resultat['choix'] == 'Abstention') {
                $abstention = $resultat['count'];
            }
        }
        return [
            'Pour' => $pour,
            'Contre' => $contre,
            'Abstention'=>$abstention
        ];
    }

    public static function getVoteByUser($idProposition, $idUtilisateur) {
        $pdo = Connexion::pdo();
        $sql = "SELECT COUNT(idVote) FROM Vote WHERE idProposition = :idProposition AND idUtilisateur = :idUtilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idProposition' => $idProposition,
            ':idUtilisateur' => $idUtilisateur
        ]);
        $resultat = $stmt->fetchColumn();
        return $resultat > 0;
    }
    
}