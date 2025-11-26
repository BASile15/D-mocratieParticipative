<?php
require_once("config/connexion.php");

class Signalement {

    private $idSignalement;
    private $idUtilisateur;
    private $idCommentaire;
    private $idProposition;
    private $raison;
    private $dateSignalement;

    public function __construct($idSignalement, $idUtilisateur, $idCommentaire, $idProposition, $raison, $dateSignalement) {
        $this->idSignalement = $idSignalement;
        $this->idUtilisateur = $idUtilisateur;
        $this->idCommentaire = $idCommentaire;
        $this->idProposition = $idProposition;
        $this->raison = $raison;
        $this->dateSignalement = $dateSignalement;
    }

    public function afficher() {
        echo "<div class='signalement-box' id='signalement-" . $this->idSignalement . "'>";
        echo "<p><strong> ID ".$this->idSignalement." Raison : </strong>" . htmlspecialchars($this->raison) . "</p>";
        echo "<div class='details' style='display:none;'>";
        echo "<p><strong>Date du signalement : </strong>" . htmlspecialchars($this->dateSignalement) . "</p>";
        echo "<p><strong>Utilisateur ID : </strong>" . htmlspecialchars($this->idUtilisateur) . "</p>";
        if ($this->idCommentaire) {
            echo "<p><strong>Commentaire ID : </strong><a href='commentaire.php?idCommentaire=" . $this->idCommentaire . "'>" . htmlspecialchars($this->idCommentaire) . "</a></p>";
        }
        if ($this->idProposition) {
            echo "<p><strong>Proposition ID : </strong><a href='routeur.php?controleur=controleurSignalement&action=afficherPSignalement&idE=" . $this->idProposition . "'>" . htmlspecialchars($this->idProposition) . "</a></p>";
        }
        echo "</div>"; 
        echo "</div><hr>";

        echo "<script>
                document.getElementById('signalement-" . $this->idSignalement . "').addEventListener('click', function() {
                    var details = this.querySelector('.details');
                    if (details.style.display === 'none') {
                        details.style.display = 'block';
                    } else {
                        details.style.display = 'none';
                    }
                });
              </script>";
    }
    
    public static function creerSignalementCommentaire($idUtilisateur, $idCommentaire, $raison) {
        $pdo = Connexion::pdo();
        $sql = "INSERT INTO Signalement (idUtilisateur, idCommentaire, raison, dateSignalement) 
                VALUES (:idUtilisateur, :idCommentaire, :raison, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idUtilisateur' => $idUtilisateur,
            ':idCommentaire' => $idCommentaire,
            ':raison' => $raison
        ]);
    }

    public static function creerSignalementProposition($idUtilisateur, $idProposition, $raison) {
        try {
            if (empty($idProposition)) {
                throw new Exception("L'ID de la proposition est manquant.");
            }
            $pdo = Connexion::pdo();
            $sql = "INSERT INTO Signalement (idSignalement,idUtilisateur, idProposition, raison, dateSignalement) 
                    VALUES (null, :idUtilisateur, :idProposition, :raison, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':idUtilisateur' => $idUtilisateur,
                ':idProposition' => $idProposition,
                ':raison' => $raison
            ]);
            if ($stmt->rowCount() > 0) {
                echo "Signalement enregistré avec succès.";
            } else {
                echo "Aucun signalement n'a été enregistré.";
            }
        } catch (Exception $e) {
            echo "Erreur lors de l'enregistrement du signalement : " . $e->getMessage();
        }
    }
    
    public static function supprimerSignalementProposition($idProposition) {
        $pdo = Connexion::pdo();
        $sql = "DELETE FROM Signalement WHERE idProposition = :idProposition";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProposition' => $idProposition]);
    }

    public static function supprimerSignalement($idProposition) {
        $pdo = Connexion::pdo();
        $sql = "DELETE FROM Signalement WHERE idProposition = :idProposition";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProposition' => $idProposition]);
    }

    public static function getSignalementsParGroupe($idGroupe) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Signalement
                WHERE idProposition IN (
                    SELECT p.idProposition FROM Proposition p
                    JOIN Theme t ON p.idTheme = t.idTheme
                    WHERE t.idGroupe = :idGroupe
                )
                OR idCommentaire IN (
                    SELECT c.idCommentaire FROM Commentaire c
                    JOIN Proposition p ON c.idProposition = p.idProposition
                    JOIN Theme t ON p.idTheme = t.idTheme
                    WHERE t.idGroupe = :idGroupe
                )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idGroupe' => $idGroupe]);
        $signalements = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $signalements[] = new Signalement(
                $row['idSignalement'], 
                $row['idUtilisateur'], 
                $row['idCommentaire'], 
                $row['idProposition'], 
                $row['raison'],        
                $row['dateSignalement'] 
            );
        }
        return $signalements;
    }

    public static function propositionSignalerParUser(){
        $pdo = Connexion::pdo();
        echo "<script>console.log('Je suis au bon endroit');</script>";
        $sql = "SELECT idProposition FROM Signalement
                WHERE idUtilisateur=:idUtilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $_SESSION['id_utilisateur']]);
        $sesPropositionSignaler = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($sesPropositionSignaler,$row['idProposition']);
        }
        return $sesPropositionSignaler;
    }
}
?>
