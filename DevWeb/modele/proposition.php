<?php
require_once("config/connexion.php");
require_once("modele/utilisateur.php");
require_once("modele/signalement.php");
require_once("modele/role.php");
class Proposition {

    private $idProposition;
    private $idUtilisateur;
    private $idTheme;
    private $titreProposition;
    private $cout;
    private $description;
    private $dateSoumission;
    private $dureeDiscussion;
    private $enVote;

    // Getters
    public function getIdProposition() { return $this->idProposition; }
    public function getIdUtilisateur() { return $this->idUtilisateur; }
    public function getIdTheme() { return $this->idTheme; }
    public function getTitreProposition() { return $this->titreProposition; }
    public function getCout() { return $this->cout; }
    public function getDescription() { return $this->description; }
    public function getDateSoumission() { return $this->dateSoumission; }
    public function getDureeDiscussion() { return $this->dureeDiscussion; }
    public function getenVote() { return $this->enVote; }

    // Setters
    public function setIdProposition($idProposition) { $this->idProposition = $idProposition; }
    public function setIdUtilisateur($idUtilisateur) { $this->idUtilisateur = $idUtilisateur; }
    public function setIdTheme($idTheme) { $this->idTheme = $idTheme; }
    public function setTitreProposition($titreProposition) { $this->titreProposition = $titreProposition; }
    public function setCout($cout) { $this->cout = $cout; }
    public function setDescription($description) { $this->description = $description; }
    public function setDateSoumission($dateSoumission) { $this->dateSoumission = $dateSoumission; }
    public function setDureeDiscussion($dureeDiscussion) { $this->dureeDiscussion = $dureeDiscussion; }
    public function setenVote($enVote) { $this->enVote = $enVote; }


    // Constructeur
    public function __construct($idProposition, $idUtilisateur, $idTheme, $titreProposition, $cout, $description, $dateSoumission, $dureeDiscussion,$enVote) {
        $this->idProposition = $idProposition;
        $this->idUtilisateur = $idUtilisateur;
        $this->idTheme = $idTheme;
        $this->titreProposition = $titreProposition;
        $this->cout = $cout;
        $this->description = $description;
        $this->dateSoumission = $dateSoumission;
        $this->dureeDiscussion = $dureeDiscussion;
        $this->enVote=$enVote;
    }

    // Affichage de la proposition
    public function afficher() {
        $user = Utilisateur::getById($this->idUtilisateur);
        $userName = ($user) ? htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']) : "Utilisateur inconnu";

        // Affichage des informations de la proposition
        echo "<section class='sectionAfficherProposition'>";
        echo "<section class='sectionInfoProposition'>";
        echo "<h3>" . htmlspecialchars($this->titreProposition) . "</h3>";
        echo "<p>Créée par : " . $userName . "</p>";
        echo "<p>Description : " . nl2br(htmlspecialchars($this->description)) . "</p>";
        echo "<p>Date de soumission : " . htmlspecialchars($this->dateSoumission) . "</p>";
        echo "<p>Durée de discussion : " . htmlspecialchars($this->dureeDiscussion) . " jours</p>";
        echo"<BR>";
        echo "</section>";
        $idProposition = $this->getIdProposition(); 
        $sesPropositionsSignaler=Signalement::propositionSignalerParUser();
        if(!$this->enVote){
            if(!in_array($idProposition,$sesPropositionsSignaler)){
            echo "<button class='btn-signalement' data-idProposition='$idProposition'>Signaler cette proposition</button>";
        
            echo "
            <div id='signalementPopup-$idProposition' class='popup' style='display:none;'>
                <div class='popup-content'>
                    <span class='close' data-idProposition='$idProposition'>&times;</span>
                    <h2>Signalement de la proposition</h2>
                    <p>Merci de donner la raison de votre signalement :</p>
                    <form id='popupForm-$idProposition' method='POST'>
                        <textarea name='raison' placeholder='Décrivez la raison du signalement' required></textarea>
                        <br>
                        <button type='submit'>Envoyer le signalement</button>
                        <input type='hidden' name='idProposition' value='$idProposition'>
                    </form>
                </div>
            </div>
            ";
            echo "<script src='js/script.js'></script>";
            $isOrg = Role::isOrganisateurInGroupe($_SESSION['id_utilisateur'],$_SESSION['idGroupe']);
            $isAdmin = Role::isAdminInGroup($_SESSION['id_utilisateur'],$_SESSION['idGroupe']);
            if($isOrg||$isAdmin)
            echo "<a href='routeur.php?controleur=controleurVote&action=lancerVote&idE=".$this->idProposition."' class='btn-signalements'>Lancer le vote</a>";
        }else {
            echo "<p>Vous avez signaler cette proposition ! </p>";
        }
        }else{
            echo "<a href='routeur.php?controleur=controleurVote&action=afficherPageVote&idE=" . $this->idProposition . "' class='btn-vote'>Voir les résultats / Voter</a>";
        }
        echo "</section>";
    }

    public function afficherSansSignaler() {
        $user = Utilisateur::getById($this->idUtilisateur);
        $userName = ($user) ? htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']) : "Utilisateur inconnu";
    
        echo "<div>";
        echo "<h3>" . htmlspecialchars($this->titreProposition) . "</h3>";
        echo "<p>Créée par : " . $userName . "</p>";
        echo "<p>Description : " . nl2br(htmlspecialchars($this->description)) . "</p>";
        echo "<p>Date de soumission : " . htmlspecialchars($this->dateSoumission) . "</p>";
        echo "<p>Durée de discussion : " . htmlspecialchars($this->dureeDiscussion) . " minutes</p>";

    }
    
    public static function userIsInGroup($idUtilisateur, $idTheme) {
        $pdo = Connexion::pdo();
        $sql = "SELECT COUNT(*) FROM appartient a INNER JOIN Theme t ON t.idGroupe=a.idGroupe WHERE idUtilisateur = :idUtilisateur AND idTheme = :idTheme";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur, ':idTheme' => $idTheme]);
        $result = $stmt->fetchColumn();
        return $result > 0;
    }

    public static function getPropositionsByTheme($idTheme) {
        if (!isset($_SESSION['id_utilisateur'])) {
            die("Vous devez être connecté pour accéder aux propositions.");
        }

        $idUtilisateur = $_SESSION['id_utilisateur'];
        if (!self::userIsInGroup($idUtilisateur, $idTheme)) {
            die("Vous n'êtes pas membre de ce groupe.");
        }
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Proposition WHERE idTheme = :idTheme";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idTheme' => $idTheme]);

        $propositions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $propositions[] = new Proposition(
                $row['idProposition'], 
                $row['idUtilisateur'], 
                $row['idTheme'], 
                $row['titreProposition'], 
                $row['cout'],
                $row['description'], 
                $row['dateSoumission'], 
                $row['dureeDiscussion'],
                $row['enVote'],
            );
        }
        return $propositions;
    }

    public static function getPropositionById($idProposition) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Proposition WHERE idProposition = :idProposition";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProposition' => $idProposition]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);  
    
        if ($row) {
            return new Proposition(
                $row['idProposition'], 
                $row['idUtilisateur'], 
                $row['idTheme'], 
                $row['titreProposition'], 
                $row['cout'], 
                $row['description'], 
                $row['dateSoumission'], 
                $row['dureeDiscussion'],
                $row['enVote'],
            );
        }
    
        return null;  
    }
    
    public static function supprimerProposition($idProposition){
        $pdo = Connexion::pdo();
        $sql = "DELETE FROM Proposition WHERE idProposition = :idProposition";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProposition' => $idProposition]);
    }

    public static function créerProposition($titreProposition, $cout,$description,$dureeDiscussion) {
        $pdo = Connexion::pdo();
        $sql = "INSERT INTO Proposition (idProposition, idUtilisateur, idTheme, titreProposition ,cout,description,dateSoumission,dureeDiscussion,enVote) 
                VALUES (null, :idUtilisateur, :idTheme, :titreProposition, :cout,:description,NOW(),:dureeDiscussion,0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idUtilisateur' => $_SESSION["id_utilisateur"],
            ':idTheme' => $_SESSION['idTheme'],
            'cout'=>$cout,
            ':titreProposition' => $titreProposition,
            ':description' => $description,
            ':dureeDiscussion' => $dureeDiscussion
        ]);
    }
}
?>
