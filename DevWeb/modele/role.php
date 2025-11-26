<?php 

class Role {
    /* ---------------- A modifier ----------------- */
    private $idRole;
    private $nomRole;
    private $descriptionRole;

    // Constructeur
    public function __construct($idRole = null, $nomRole = '', $descriptionRole = '') {
        $this->idRole = $idRole;
        $this->nomRole = $nomRole;
        $this->descriptionRole = $descriptionRole;
    }

    // Getters
    public function getIdRole() { return $this->idRole; }
    public function getNomRole() { return $this->nomRole; }
    public function getDescriptionRole() { return $this->descriptionRole; }

    // Setters
    public function setIdRole($idRole) { $this->idRole = $idRole; }
    public function setNomRole($nomRole) { $this->nomRole = $nomRole; }
    public function setDescriptionRole($descriptionRole) { $this->descriptionRole = $descriptionRole; }

	public static function getAllRoles() {
		$requete = "SELECT * FROM Role;";
		$resultat = connexion::pdo()->query($requete);
		$resultat->setFetchMode(PDO::FETCH_ASSOC);
		$rolesArray = $resultat->fetchAll();
		$lesRoles = [];
		foreach ($rolesArray as $roleData) {
			$role = new Role($roleData['idRole'], $roleData['nomRole'], $roleData['descriptionRole']);
			$lesRoles[] = $role;
		}
		return $lesRoles;
	}
	
    public static function isAdminInGroup($idUtilisateur, $idGroupe) {
        $pdo = Connexion::pdo();
        $sql = "SELECT idRole FROM appartient WHERE idUtilisateur = :idUtilisateur AND idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur, ':idGroupe' => $idGroupe]);
        $role = $stmt->fetchColumn();
        if ($role === false) {
            return false;
        }
        return $role == 2;
    }

    public static function isModeratorInGroupe($idUtilisateur, $idGroupe) {
        $pdo = Connexion::pdo();
        $sql = "SELECT idRole FROM appartient WHERE idUtilisateur = :idUtilisateur AND idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur, ':idGroupe' => $idGroupe]);
        $role = $stmt->fetchColumn();
        if ($role === false) {
            return false;
        }
        return $role == 3;
    }
    public static function isOrganisateurInGroupe($idUtilisateur, $idGroupe) {
        $pdo = Connexion::pdo();
        $sql = "SELECT idRole FROM appartient WHERE idUtilisateur = :idUtilisateur AND idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur, ':idGroupe' => $idGroupe]);
        $role = $stmt->fetchColumn();
        if ($role === false) {
            return false;
        }
        return $role == 4;
    }

    // Change le rôle d'un utilisateur dans un groupe
    public static function changerRoleUser($idUtilisateur, $idGroupe, $idRole) {
        $pdo = Connexion::pdo();
        if($idRole==2){
            $sql = "SELECT idUtilisateur FROM appartient WHERE idGroupe = :idGroupe AND idRole=:idRole";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':idGroupe' => $idGroupe,':idRole'=>2]);
            $leChef = $stmt->fetchColumn();
            $sql = "CALL cederGroupe(:idAdmin, :idUtilisateur, :idGroupe);";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':idUtilisateur' => $idUtilisateur,
                ':idGroupe' => $idGroupe,
                ':idAdmin' => $leChef
            ]);
        }else{
        $sql = "UPDATE appartient SET idRole = :idRole WHERE idUtilisateur = :idUtilisateur AND idGroupe = :idGroupe";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idUtilisateur' => $idUtilisateur,
            ':idGroupe' => $idGroupe,
            ':idRole' => $idRole
        ]);
    }
        return $stmt->rowCount() > 0;
    }
}
?>
