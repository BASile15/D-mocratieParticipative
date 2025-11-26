<?php 

require_once('modele/role.php');	
$sonId = isset($_SESSION['idUtilisateur']);
class Groupe {
	private $idGroupe;
	private $nomGroupe;
	private $MDPGroupe;
	private $budgetGroupe;
	private $descriptionGroupe;
	private $imageGroupe;
	private $couleurGroupe;
	private $dateCreationGroupe;

	// Constructeur
	public function __construct($id = null, String $n = "", String $mdp = "", int $b = 0, String $d = "", String $i = "", String $c = "", String $dcg = "") {
		$this->idGroupe = $id;
		$this->nomGroupe = $n;
		$this->MDPGroupe = $mdp;
		$this->budgetGroupe =0;
		$this->descriptionGroupe = $d;
		$this->imageGroupe = $i;
		$this->couleurGroupe = $c;
		$this->dateCreationGroupe = $dcg;
	}
				
	// Getters
	public function getIdGroupe() {return $this->idGroupe;}
	public function getNomGroupe() {return $this->nomGroupe;}
	public function getMDPGroupe() {return $this->MDPGroupe;}
	public function getBudgetGroupe() {return $this->budgetGroupe;}
	public function getDescriptionGroupe() {return $this->descriptionGroupe;}
	public function getImageGroupe() {return $this->imageGroupe;}
	public function getCouleurGroupe() {return $this->couleurGroupe;}
	public function getDateCreationGroupe() {return $this->dateCreationGroupe;}

	// Setters
	public function setIdGroupe($valeur) {$this->idGroupe = $valeur;}
	public function setNomGroupe($valeur) {$this->nomGroupe = $valeur;}
	public function setMDPGroupe($valeur) {$this->MDPGroupe = $valeur;}
	public function setBudgetGroupe($valeur) {$this->budgetGroupe = $valeur;}
	public function setDescriptionGroupe($valeur) {$this->descriptionGroupe = $valeur;}
	public function setImageGroupe($valeur) {$this->imageGroupe = $valeur;}
	public function setCouleurGroupe($valeur) {$this->couleurGroupe = $valeur;}
	public function setDateCreationGroupe($valeur) {$this->dateCreationGroupe = $valeur;}

	public static function getGroupeUser() {
		if (isset($_SESSION['id_utilisateur'])) {
			$utilisateur_id = $_SESSION['id_utilisateur'];
			$pdo = Connexion::pdo();
			$requete = "SELECT g.idGroupe, g.nomGroupe, g.MDP AS MDPGroupe, g.budgetGlobal AS budgetGroupe, g.description AS descriptionGroupe, g.imageGroupe AS imageGroupe, g.couleurGroupe, g.dateCreation AS dateCreationGroupe 
						FROM Groupe g 
						INNER JOIN appartient a ON a.idGroupe = g.idGroupe 
						WHERE a.idUtilisateur = :idUtilisateur";
			$stmt = $pdo->prepare($requete);
			$stmt->execute([':idUtilisateur' => $utilisateur_id]);
			$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC); 
       		return $resultats;

		}
	}

	public static function getUsersInGroup($idGroupe) {
		$pdo = Connexion::pdo();
		$requete = "SELECT u.idUtilisateur, u.nom, u.prenom, u.email, r.idRole, r.nomRole
					FROM Utilisateur u 
					JOIN appartient a ON u.idUtilisateur = a.idUtilisateur 
					JOIN Role r ON a.idRole = r.idRole 
					WHERE a.idGroupe = :idGroupe;";
		$stmt = $pdo->prepare($requete);
		$stmt->execute([':idGroupe' => $idGroupe]);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		return $stmt->fetchAll();
	}

	public static function getRoleDansGroupe($idGroupe){
		$utilisateur_id = $_SESSION['id_utilisateur'];
		$pdo = Connexion::pdo();			
		$sql = "SELECT idRole FROM appartient a INNER JOIN Groupe g on a.idGroupe = g.idGroupe WHERE a.idGroupe= :idG AND a.idUtilisateur = :idU;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':idG' => $idGroupe, ':idU' => $utilisateur_id]);
		$resultat = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($resultat) {
			return $resultat['idRole'];
		} else {
			return null;
		}
	}

	public static function checkGroupeExists($nom) {
		$pdo = Connexion::pdo();
		$sql = "SELECT COUNT(*) FROM Groupe WHERE nomGroupe = :nom";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':nom' => $nom]);
		return $stmt->fetchColumn() > 0;
	}

	public function save() {
		$pdo = Connexion::pdo();
		$sql = "INSERT INTO Groupe (idGroupe, nomGroupe, MDP, budgetGlobal, description, imageGroupe, couleurGroupe, dateCreation)
				VALUES (:id, :nom, :mdp, :budget, :description, :image, :couleur, :dcg)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':id' => $this->idGroupe,
			':nom' => $this->nomGroupe,
			':mdp' => $this->MDPGroupe,
			':budget' => $this->budgetGroupe,
			':description' => $this->descriptionGroupe,
			':image' => $this->imageGroupe,
			':couleur' => $this->couleurGroupe,
			':dcg' => $this->dateCreationGroupe
		]);
	}

	// Lors de la création d'un groupe, pour ajouter automatiquement le créateur à un groupe en temps qu'administrateur
	public static function AjoutAutoDansGroupe($idU, $idG) {
		$date = date('Y-m-d H:i:s');
		$pdo = Connexion::pdo();
		$sql = "INSERT INTO appartient(idUtilisateur, idGroupe, idRole, dateAttribution) 
				VALUES (:idU, :idG, 2, :date)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':idU' => $idU,
			':idG' => $idG,
			':date' => $date
		]);
	}

	// Recupere l'id d'un groupe passé en parametres
	public static function GetIdGroupeViaNom($nomGroupe) {
		$pdo = Connexion::pdo();			
		$sql = "SELECT idGroupe FROM Groupe WHERE nomGroupe = :nom;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':nom' => $nomGroupe]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			return $result['idGroupe'];
		} else {
			return null;
		}
	}

	// Recupere le nom d'un groupe dont l'id est passé en parametre
	public static function GetNomGroupeViaId($idGroupe) {
		$pdo = Connexion::pdo();			
		$sql = "SELECT nomGroupe FROM Groupe WHERE idGroupe = :idG;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':idG' => $idGroupe]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			return $result['nomGroupe'];
		} else {
			return null;
		}
	}
	public static function kickUser($idGroupe, $idUtilisateur) {
		try {
			$pdo = Connexion::pdo();
			$sql = "DELETE FROM appartient WHERE idGroupe = :idGroupe AND idUtilisateur = :idUtilisateur;";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':idGroupe' => $idGroupe, ':idUtilisateur' => $idUtilisateur]);
			return $stmt->rowCount() > 0;
		} catch (Exception $e) {
			echo "Erreur SQL : " . $e->getMessage();
			return false;
		}
	}
	
	
	
}
?>