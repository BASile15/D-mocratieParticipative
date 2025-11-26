<?php
require_once("modele/vote.php");

class ControleurVote {

    public static function Voter() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idProposition']) && isset($_POST['choix'])) {
            if (!isset($_SESSION['id_utilisateur'])) {
                echo "Vous devez être connecté pour voter.";
                return;
            }
            $idProposition = (int)$_POST['idProposition'];
            $idUtilisateur = $_SESSION['id_utilisateur'];  
            $choix = htmlspecialchars($_POST['choix']);
            $choixValides = ['Pour', 'Contre', 'Abstention'];
            if (!in_array($choix, $choixValides)) {
                echo "Choix de vote invalide.";
                return;
            }
            if ($idProposition <= 0) {
                echo "Proposition invalide.";
                return;
            }
            Vote::creerVote($idProposition, $choix);
            header('Location: routeur.php?controleur=controleurVote&action=afficherPageVote&idE=' . $idProposition);
            exit;
        } else {
            echo "Formulaire non soumis correctement.";
        }
    }

    // Afficher la page de vote
    public static function afficherPageVote() {
        if (!isset($_SESSION['id_utilisateur'])) {
            echo "Vous devez être connecté pour voir cette page.";
            return;
        }

        $idProposition = $_GET['idE'];
        $userVote = Vote::getVoteByUser($idProposition, $_SESSION['id_utilisateur']);

        $title = "Vote";
        include("vue/debut.php");
        include("vue/menu.html");
        if ($userVote) {
            self::afficherResultatsVote($idProposition);
        } else {
            include("vue/formulaires/formVote.html");
        }

        include("vue/fin.html");
    }

    public static function afficherResultatsVote($idProposition) {
        $resultats = Vote::getResultatsVote($idProposition);
        $pour = $resultats['Pour'] ?? 0;
        $abstention = $resultats['Abstention'] ?? 0;
        $contre = $resultats['Contre'] ?? 0;     
        $totalVotes = $pour + $abstention + $contre;
    
        if ($totalVotes === 0) {
            echo "<p>Aucun vote n'a été enregistré pour cette proposition.</p>";
            return;
        }

        echo "
        <h3>Résultats du vote</h3>
        <canvas id='voteChart'></canvas>
        <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
        <script src='js/voteChart.js'></script>
        <script>
            initializeChart({
                Pour: $pour,
                Abstention: $abstention,
                Contre: $contre,
                totalVotes: $totalVotes
            });
        </script>";
        echo"<BR>";
        echo "<button class='btn-retour' onclick='history.back()'>Retour</button>";
    }

    public static function lancerVote($idProposition) {
        Vote::lancerVote($idProposition);
        self::afficherPageVote();
        exit;
    }
    
}
?>
