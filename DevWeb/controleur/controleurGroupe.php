<?php
require_once("modele/groupe.php");
require_once("modele/role.php");

class controleurGroupe {
    public static function lireGroupesUtilisateur() {
        $title = "Groupes de l'utilisateur";
        include("vue/debut.php");
        include("vue/menu.html");
        $groupes = Groupe::getGroupeUser();
        if (!$groupes) {
            include("vue/groupe/aucunGroupe.php");
        } else {
            include("vue/groupe/lesGroupes.php");
        }
        include("vue/fin.html");
    }

	public static function CreerGroupe() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$title ='Créer un groupe';
			$nom = $_POST['nom'];
			$description = $_POST['description'];
			$budget = 0;
			$image = $_POST['image']; 
			$couleur = $_POST['couleur'];
			$mdp = $_POST['mdp'];
			$confirme_mdp = $_POST['confirme_mdp'];
            $dateCreation = date('Y-m-d H:i:s');
			if ($mdp !== $confirme_mdp) {
				echo "Les mots de passe ne correspondent pas.";
				header("Location: routeur.php?controleur=controleurUtilisateur&action=formCreerGroupe");
				return;
			}
			if (Groupe::checkGroupeExists($nom)) {
				echo "Un groupe du meme nom existe deja.";
                header("Location: routeur.php?controleur=controleurUtilisateur&action=formCreerGroupe");
				return;
			}
			$mdp_hache = password_hash($mdp, PASSWORD_BCRYPT);
            $NouveauGroupe = new Groupe(null, $nom, $mdp_hache, $budget, $description, $image, $couleur, $dateCreation);
			$NouveauGroupe->save();

			$IdNouveauGroupe = Groupe::GetIdGroupeViaNom($nom);
			$NouveauGroupe->setIdGroupe($IdNouveauGroupe);
			$idUtilisateur = $_SESSION['id_utilisateur'];
			Groupe::AjoutAutoDansGroupe($idUtilisateur, $IdNouveauGroupe);
			header("Location: routeur.php?controleur=controleurGroupe&action=lireGroupesUtilisateur");
		} else {
			header("Location: routeur.php?controleur=controleurUtilisateur&action=formCreerGroupe");
		}
	}	
	
	public static function lireUtilisateursDuGroupe($idGroupe) {
		$title = "Utilisateurs dans le groupe";
		include("vue/debut.php");
		include("vue/menu.html");
		$users = Groupe::getUsersInGroup($idGroupe);
		$roles = Role::getAllRoles();
		if (!$users) {
			echo "<p>Erreur : Aucun utilisateur trouvé dans ce groupe.</p>";
		} else {
			include("vue/groupe/lesUtilisateursDuGroupe.php");
		}
		include("vue/fin.html");
	}

	public static function expulserUtilisateur() {
		if (empty($_POST['idUtilisateur']) || empty($_POST['idGroupe'])) {
			echo "Utilisateur ou groupe manquant.";
			return;
		}
		$idUtilisateur = htmlspecialchars($_POST['idUtilisateur']);
		$idGroupe = htmlspecialchars($_POST['idGroupe']);
		if (Groupe::kickUser($idGroupe, $idUtilisateur)) {
			echo "Utilisateur expulsé avec succès.";
			header("Location: routeur.php?controleur=controleurGroupe&action=lireUtilisateursDuGroupe&idE=$idGroupe");
			exit;
		} else {
			echo "Erreur lors de l'expulsion de l'utilisateur.";
		}
	}
	
}
?>
