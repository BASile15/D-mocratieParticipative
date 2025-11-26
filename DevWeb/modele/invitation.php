<?php
require_once("config/connexion.php");

class Invitation {
	private $idInvitation;
	private $emailInvite; 
	private $idGroupe;
	private $idUtilisateur; 
	private $token;
	private $etatInvitation;
	private $dateEnvoi;

	// Constructeur
	public function __construct(int $idI = NULL, String $e = "", String $idG = "", int $idU = NULL, String $t = "", String $etat = "", String $date = null) {
		$this->idInvitation = $idI;
		$this->emailInvite = $e;
		$this->idGroupe = $idG;
		$this->idUtilisateur = $idU;
		$this->token = $t;
		$this->etatInvitation = $etat;
		$this->dateEnvoi = $date;
	}

	// Getters
	public function getIdInvitation() {return $this->idInvitation;}
	public function getEmailInvite() {return $this->emailInvite;}
	public function getIdGroupe() {return $this->idGroupe;}
	public function getIdUtilisateur() {return $this->idUtilisateur;}
	public function getToken() {return $this->token;}
	public function getEtatInvitation() {return $this->etatInvitation;}
	public function getdateEnvoi() {return $this->dateEnvoi;}

	// Setters
	public function setIdInvitation($valeur) {$this->idInvitation = $valeur;}
	public function setEmailInvite($valeur) {$this->emailInvite = $valeur;}
	public function setIdGroupe($valeur) {$this->idGroupe = $valeur;}
	public function setIdUtilisateur($valeur) {$this->idUtilisateur = $valeur;}
	public function setToken($valeur) {$this->token = $valeur;}
	public function setEtatInvitation($valeur) {$this->etatInvitation = $valeur;}
	public function setDateEnvoi($valeur) {$this->dateEnvoi = $valeur;}

	// methode pour afficher les invitations de l'utilisateur
	public function afficher() {
		if ($this->getEtatInvitation()==="en attente"){
			echo "<section class='lienAffichageNotif'>";
				echo "<section class='sectionAfficherNotif'>";
					echo "<h3>Vous avez été invité à rejoindre : ".htmlspecialchars($this->getToken())."</h3>";
					echo "<p>Invitation envoyé le ".htmlspecialchars($this->getdateEnvoi())."</p>";
					echo "<p><a href='routeur.php?controleur=controleurInvitation&action=AccepterInvitation&idE={$this->getIdInvitation()}'>Accepter</a>";
					echo "<a href='routeur.php?controleur=controleurInvitation&action=RefuserInvitation&idE={$this->getIdInvitation()}'>Refuser</a></p>";
				echo "</section>";
			echo "</section>";
		}
	}

	public function save() {
		$pdo = Connexion::pdo();
		$sql = "INSERT INTO Invitation (idInvitation, emailInvite, idGroupe, idUtilisateur, token, etatInvitation, dateEnvoi)
				VALUES (null, :email, :idGroupe, :idUtilisateur, :token, :etat, :date)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			'email' => $this->getEmailInvite(),
			'idGroupe' => $this->getIdGroupe(),
			'idUtilisateur' => $this->getIdUtilisateur(),
			'etat' => $this->getEtatInvitation(),
			'token' => $this->getToken(),
			'date' => $this->getdateEnvoi()
		]);
	}

    // Pour avoir toutes les invitations de l'utilisateur courant
	public static function getInvitationUser() {
		if (isset($_SESSION['id_utilisateur'])) {
			$utilisateur_id = $_SESSION['id_utilisateur'];
			$pdo = Connexion::pdo();
			$requete = "SELECT * FROM Invitation WHERE idUtilisateur = :idutilisateur AND etatInvitation='en attente';";
			$stmt = $pdo->prepare($requete);
			$stmt->execute([':idutilisateur' => $utilisateur_id]);
			$lesNotifications = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$lesNotifications[] = new Invitation(
					$row['idInvitation'], 
					$row['emailInvite'], 
					$row['idGroupe'], 
					$row['idUtilisateur'],
					$row['token'],
					$row['etatInvitation'],
					$row['dateEnvoi']
				);
			}
			return $lesNotifications;
		}
	}

    // Pour verifier si l'utilisateur à qui on veut envoyer l'invitation existe
    public static function UtilisateurExiste($email) {
        $pdo = Connexion::pdo();
        $sql = "SELECT idUtilisateur FROM Utilisateur WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $idUtilisateur = $stmt->fetchColumn();
        return $idUtilisateur;
    }

    // Verifie si l'utilisateur a deja une invitation "en attente" pour ce groupe
    public static function UtilisateurDejaInvite($email, $idGroupe) {
        $pdo = Connexion::pdo();
        $sql = "SELECT COUNT(*) FROM Invitation WHERE emailInvite = :email AND idGroupe= :idGroupe AND etatInvitation = 'en attente'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':idGroupe' => $idGroupe
        ]);
        return $stmt->fetchColumn() > 0;
    }

    // Pour trouver l'ID d'un utilisateur Via son email
    public static function TrouverIdUtilisateur($email) {
        $pdo = Connexion::pdo();			
        $sql = "SELECT idUtilisateur FROM Utilisateur WHERE email = :email;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['idUtilisateur'];
        } else {
            return 0;
        }
    }

    // Pour recupere toutes les informations d'une invitation
    public static function InfoInvitation($idInvitation) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Invitation WHERE idInvitation = :idInvitation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idInvitation' => $idInvitation]);
        $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
        return $invitation;
    }
    
    

    // Pour change l'etat de la notification en accepté ou refusé
    public static function accepter($idInvitation) {
        $pdo = Connexion::pdo();
        $sql = "UPDATE Invitation SET etatInvitation = 'acceptée' WHERE idInvitation = :idInvitation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idInvitation' => $idInvitation]);
    }

    public static function refuser($idInvitation) {
        $pdo = Connexion::pdo();
        $sql = "UPDATE Invitation SET etatInvitation = 'refusée' WHERE idInvitation = :idInvitation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idInvitation' => $idInvitation]);
    }

    public static function ajouterUtilisateurDansGroupe($idU, $idG) {
        $pdo = Connexion::pdo();
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO appartient(idUtilisateur, idGroupe, idRole, dateAttribution) 
                VALUES (:idU, :idG, 1, :date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idU' => $idU,
            ':idG' => $idG,
            ':date' => $date
        ]);
    }


    public static function getInvitationById($idInvitation) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Invitation WHERE idInvitation = :idInvitation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idInvitation' => $idInvitation]);
        $data = $stmt->fetch();
        return new Invitation($data['idInvitation'], $data['emailInvite'], $data['idGroupe'], $data['idUtilisateur'], $data['token'], $data['etatInvitation'], $data['dateEnvoi']);
    }

    public static function getInvitationsParGroupe($idGroupe) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Invitation WHERE idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idGroupe' => $idGroupe]);
        $result = [];
        foreach ($stmt as $row) {
            $result[] = new Invitation($row['idInvitation'], $row['emailInvite'], $row['idGroupe'], $row['idUtilisateur'], $row['token'], $row['etatInvitation'], $row['dateEnvoi']);
        }
        return $result;
    }

    public static function mettreAJourEtat($idInvitation, $nouvelEtat) {
        $pdo = Connexion::pdo();
        $sql = "UPDATE Invitation SET etatInvitation = :etatInvitation WHERE idInvitation = :idInvitation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':etatInvitation' => $nouvelEtat, ':idInvitation' => $idInvitation]);
    }

    public static function supprimerInvitation($idInvitation) {
        $pdo = Connexion::pdo();
        $sql = "DELETE FROM Invitation WHERE idInvitation = :idInvitation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idInvitation' => $idInvitation]);
    }
}
?>
