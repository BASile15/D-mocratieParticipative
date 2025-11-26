<?php
require_once("modele/invitation.php");
require_once("modele/groupe.php");

class controleurInvitation {

    public static function LireInvitations() {
        $title = "Invitations de l'utilisateur";
        include("vue/debut.php");
        include("vue/menu.html");
        $invitations = Invitation::getInvitationUser();
        if (!$invitations) {
            include("vue/invitation/aucuneInvitation.php");
        } else {
            include("vue/invitation/lesInvitations.php");
        }
        include("vue/fin.html");
    }

	public static function AfficherFormulaireInvitation() {
		$title = "Formulaire d'invitation";
		include("vue/debut.php");
        include("vue/menu.html");
		$idGroupe = $_GET['idGroupe'];
		if (Groupe::getRoleDansGroupe($idGroupe) === 2) {
			require "vue/formulaires/formInviterUtilisateur.html";
		} else {
			echo "Accès refusé : vous n'êtes pas administrateur de ce groupe.";
		}
		include("vue/fin.html");
	}

	// Pour créer une nouvelle invitation
	public static function NouvelleInvitation() {
		require_once("modele/groupe.php");
		$email = $_POST['email'];
		$idGroupe = $_POST['idGroupe'];
		$UtilisateurExiste = Invitation::UtilisateurExiste($email);
		$UtilisateurDejaInvite = Invitation::UtilisateurDejaInvite($email, $idGroupe);
		if (!$UtilisateurExiste){
			echo "Utilisateur non trouvé ! Veuillez vérifier l'email saisi.";
			header("Location: routeur.php?controleur=controleurInvitation&action=AfficherFormulaireInvitation&idGroupe=$idGroupe");
			return;
		}
		if ($UtilisateurDejaInvite) {
			echo "Utilisateur deja invité dans ce groupe !";
			header("Location: routeur.php?controleur=controleurInvitation&action=AfficherFormulaireInvitation&idGroupe=$idGroupe");
			return;
		}
		$idUtilisateur = Invitation::TrouverIdUtilisateur($email);
		$token = Groupe::GetNomGroupeViaId($idGroupe);
		$date = date('Y-m-d H:i:s');
		$invitation = new Invitation(null, $email, $idGroupe, $idUtilisateur, $token, "en attente", $date);
        $invitation->save();
		header("Location: routeur.php?controleur=controleurGroupe&action=lireGroupesUtilisateur");
		
	}

	public static function RefuserInvitation() {
		$idInvitation = $_GET['idInvitation'];
		$invitation = Invitation::InfoInvitation($idInvitation);
		if ($invitation) {
			$invitation->refuser($idInvitation);
			header("Location: routeur.php?controleur=controleurInvitation&action=LireInvitations");
			exit;
		} else {
			echo "Invitation non trouvée";
		}
	}
	public static function InfoInvitation($idInvitation) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Invitation WHERE idInvitation = :idInvitation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idInvitation' => $idInvitation]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, "Invitation");
        $invitation = $stmt->fetch();
        return $invitation;
    }
	public static function AccepterInvitation() {
		$idInvitation = $_GET['idE'];
		$invitation = Invitation::InfoInvitation($idInvitation);
		if ($invitation) {
			$idG=$invitation["idGroupe"];
			$idU=$invitation["idUtilisateur"];
			if (empty($idG) || empty($idU)) {
				echo "Erreur : IdGroupe ou IdUtilisateur manquant.";
				echo "<p>id grp = $idG</p>";
				echo "<p>id u = $idG</p>";
				exit;
			}
			Invitation::ajouterUtilisateurDansGroupe($idU, $idG);
			Invitation::accepter($idInvitation);
			header("Location: routeur.php?controleur=controleurGroupe&action=lireGroupesUtilisateur");
			exit;
		} else {
			echo "Invitation non trouvée";
			exit;
		}
	}
}
?>
