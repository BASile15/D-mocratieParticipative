<?php
require_once("modele/utilisateur.php");

class controleurUtilisateur {

    public static function formConnexion() {
        $title = "Connexion";
        include("vue/debut.php");
        include("vue/formulaires/formConnexion.html");
        include("vue/finSansFooter.html");
    }

    public static function formConnexionWERR() {
        $title = "Connexion";
        include("vue/debut.php");
        include("vue/formulaires/formConnexionWERR.php");
        include("vue/finSansFooter.html");
    }

    public static function formInscription() {
        $title = "Inscription";
        include("vue/debut.php"); 
        include("vue/formulaires/formInscription.html");
        include("vue/finSansFooter.html");
    }

    public static function formCreerGroupe() {
        $title = "CreerGroupe";
        include("vue/debut.php");
        include("vue/formulaires/formCreerGroupe.html");
        include("vue/finSansFooter.html");
    }

    public static function connexion() {
        if (self::estConnecte()) {
            header("Location: routeur.php?controleur=controleurGroupe&action=lireGroupesUtilisateur");
            exit;
        }
        $email = $_POST['email'] ?? null;
        $motDePasse = $_POST['password'] ?? null;
        if ($email && $motDePasse) {
            $utilisateur = Utilisateur::connecter($email, $motDePasse);
            if ($utilisateur) {
                session_start();
                $_SESSION['id_utilisateur'] = $utilisateur['idUtilisateur'];
                header("Location: routeur.php?controleur=controleurGroupe&action=lireGroupesUtilisateur");
                exit;
            } else {
                self::formConnexion();
                echo "<script src='js/popupFailed.js'></script>";
            }
        } else {
            $messageErreur = "Veuillez remplir tous les champs.";
            self::formConnexion();
        }
    }

    public static function inscription() {
        if (self::estConnecte()) {
            header("Location: routeur.php?controleur=controleurUtilisateur&action=MonProfil");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $adresse = $_POST['adresse'];
            $ville = $_POST['ville'];
            $mdp = $_POST['mdp'];
            $confirme_mdp = $_POST['confirme_mdp'];
            if ($mdp !== $confirme_mdp) {
                echo '<script type="text/javascript">alert("Les mots de passe ne correspondent pas.");</script>';
                header("Location: routeur.php?controleur=controleurUtilisateur&action=formInscription");
                exit;
            }
            if (Utilisateur::checkEmailExists($email)) {
                echo "Cet email est déjà utilisé.";
                return;
            }
            $mdp_hache = password_hash($mdp, PASSWORD_BCRYPT);
            $utilisateur = new Utilisateur($email, $nom, $prenom, $adresse, $ville, $mdp_hache);
            $utilisateur->save();

            $nouvelUtilisateur = Utilisateur::connecter($email, $mdp);
            if ($nouvelUtilisateur) {
                session_start();
                $_SESSION['id_utilisateur'] = $nouvelUtilisateur['idUtilisateur'];
                header("Location: routeur.php?controleur=controleurGroupe&action=lireGroupesUtilisateur");
                exit;
            } else {
                echo '<script type="text/javascript">alert("Une erreur est survenue lors de la connexion automatique.");</script>';
                header("Location: routeur.php?controleur=controleurUtilisateur&action=connexion");
                exit;
            }
        } else {
            $title = "Inscription";
            include("vue/debut.php");
            include("vue/menu.html");
            include("vue/formulaires/formInscription.html");
            include("vue/fin.html");
        }
    }
    

    public static function deconnecterUtilisateur() {
        session_start();
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header("Location: routeur.php?controleur=controleurUtilisateur&action=formConnexion");
        exit;
    }

    public static function deconnecterUtilisateurWErr() {
        session_start();
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header("Location: routeur.php?controleur=controleurUtilisateur&action=formConnexionWERR");
        exit;
    }
    
    public static function estConnecte() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['id_utilisateur']);
    }

    public static function MonProfil() {
        if (!self::estConnecte()) {
            echo "Erreur : Vous devez être connecté pour voir votre profil.";
            header("Location: routeur.php?controleur=controleurUtilisateur&action=formConnexion");
            exit;
        }

        $title = "Mon Profil";
        include("vue/debut.php"); 
        include("vue/menu.html");
        $infos = Utilisateur::mesInfos(); 
        if (!$infos) {
            include("vue/utilisateur/erreur.php");
        } else  {
            include("vue/utilisateur/TableauInfoUtilisateur.php");
        }
        include("vue/fin.html");
    }

    public static function formModifierInfoUtilisateur() {
        if (!self::estConnecte()) {
            echo "Erreur : Vous devez être connecté pour modifier vos informations.";
            header("Location: routeur.php?controleur=controleurUtilisateur&action=formConnexion");
            exit;
        }
        $title = "Modification des informations";
        include("vue/debut.php");
        $infos = Utilisateur::mesInfos();  
        if (!$infos) {
            include("vue/utilisateur/erreur.php");
        } else  {
            include("vue/formulaires/formModifierInfoUtilisateur.html");
        }
    }

    public static function modifierProfil() {
        if (!self::estConnecte()) {
            echo "Erreur : Vous devez être connecté pour modifier votre profil.";
            header("Location: routeur.php?controleur=controleurUtilisateur&action=formConnexion");
            exit;
        }
        $title = "Modification du profil";
        include("vue/debut.php");
        include("vue/menu.html");
        $infos = Utilisateur::getById($_SESSION['id_utilisateur']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $adresse = $_POST['adresse'];
            $ville = $_POST['ville'];
            $mot_de_passe_actuel = $_POST['mot_de_passe_actuel'];
            $nouveau_mdp = $_POST['mdp'];
            $resultat = Utilisateur::update($email, $nom, $prenom, $adresse, $ville, $mot_de_passe_actuel, $nouveau_mdp);
            if ($resultat) {
                echo "Les informations ont été mises à jour avec succès!";
                header("Location: routeur.php?controleur=controleurUtilisateur&action=MonProfil");
                exit;
            } else {
                echo "Erreur: Impossible de mettre à jour les informations. Vérifiez vos données.";
                header("Location: routeur.php?controleur=controleurUtilisateur&action=formModifierInfoUtilisateur");
            }
        }
        include("vue/utilisateur/formModifierProfil.php");
        include("vue/fin.html");
    }
}
?>
