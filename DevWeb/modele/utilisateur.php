<?php
require_once("config/connexion.php");

class Utilisateur {

    private $email;
    private $nom;
    private $prenom;
    private $adresse;
    private $ville;
    private $mdp;

    // Getters
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getAdresse() { return $this->adresse; }
    public function getVille() { return $this->ville; }

    // Setters
    public function setNom($valeur) { $this->nom = $valeur; }
    public function setPrenom($valeur) { $this->prenom = $valeur; }
    public function setAdresse($valeur) { $this->adresse = $valeur; }
    public function setVille($valeur) { $this->ville = $valeur; }

    /* ----- Constructeur ----- */
    public function __construct(String $email = NULL, String $nom = "", String $prenom = "", String $adresse = "", String $ville = "", String $mdp = NULL) {
        if (!is_null($email)) {
            $this->email = $email;
            $this->nom = $nom;
            $this->prenom = $prenom;
            $this->adresse = $adresse;
            $this->ville = $ville;
            $this->mdp = $mdp;
        }
    }

    /* ----- Connexion de l'utilisateur ----- */
    public static function connecter($email, $motDePasse) {
        $pdo = Connexion::pdo();
        $sql = "SELECT * FROM Utilisateur WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($motDePasse, $utilisateur['motDePasse'])) {
            return $utilisateur;
        }
        return false;
    }

    public static function mesInfos() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['id_utilisateur'])) {
            $pdo = Connexion::pdo();
            $requete = "SELECT * FROM Utilisateur WHERE idUtilisateur = :id";
            $stmt = $pdo->prepare($requete);
            $stmt->execute([':id' => $_SESSION['id_utilisateur']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        }
        return null;
    }
    
    public function save() {
        $pdo = Connexion::pdo();
        $sql = "INSERT INTO Utilisateur (idUtilisateur, email, nom, prenom, adressePostale, ville, motDePasse)
                VALUES (null, :email, :nom, :prenom, :adresse, :ville, :mdp)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email' => $this->email,
            ':nom' => $this->nom,
            ':prenom' => $this->prenom,
            ':adresse' => $this->adresse,
            ':ville' => $this->ville,
            ':mdp' => $this->mdp
        ]);
    }

    public static function update($email, $nom, $prenom, $adresse, $ville, $mot_de_passe_actuel = null, $nouveau_mdp = null) {
        $pdo = Connexion::pdo();

        if ($mot_de_passe_actuel) {
            $sql = "SELECT motDePasse FROM Utilisateur WHERE idUtilisateur = :idUtilisateur";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':idUtilisateur' => $_SESSION["id_utilisateur"]]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $motDePasseActuel = $user['motDePasse'];
            } else {
                echo "Erreur : Impossible de récupérer les informations de l'utilisateur.";
                exit;
            }

            if (!password_verify($mot_de_passe_actuel, $user['motDePasse'])) {
                return false;
            }

            if ($nouveau_mdp && $nouveau_mdp !== $_POST['confirme_mdp']) {
                return false;
            }

            if ($nouveau_mdp) {
                $nouveau_mdp = password_hash($nouveau_mdp, PASSWORD_BCRYPT);
            }
        }

        $sql = "UPDATE Utilisateur 
                SET email = :email, 
                    nom = :nom, 
                    prenom = :prenom, 
                    adressePostale = :adresse, 
                    ville = :ville, 
                    motDePasse = :motDePasse
                WHERE idUtilisateur = :idUtilisateur";

        $stmt = $pdo->prepare($sql);
        $resultat = $stmt->execute([
            ':idUtilisateur' => $_SESSION["id_utilisateur"],
            ':email' => $email,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':adresse' => $adresse,
            ':ville' => $ville,
            ':motDePasse' => $nouveau_mdp ? $nouveau_mdp : $user['motDePasse'] 
        ]);

        return $resultat;
    }

    public function afficher() {
        echo "<pre>";
        echo $this->nom;
        echo $this->prenom;
        echo "\nDescription : ";
        echo "</pre>";
    }

    public static function checkEmailExists($email) {
        $pdo = Connexion::pdo();
        $sql = "SELECT COUNT(*) FROM Utilisateur WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    public static function getById($idUtilisateur) {
        $pdo = Connexion::pdo(); 
        $sql = "SELECT * FROM Utilisateur WHERE idUtilisateur = :idUtilisateur"; 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idUtilisateur' => $idUtilisateur]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
