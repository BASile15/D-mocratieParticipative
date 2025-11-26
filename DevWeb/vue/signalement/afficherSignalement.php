<h1>Signalements du groupe</h1>

<?php
$idE = isset($_GET['idE']) ;
if ($idE !== null) {
    echo "<a href='routeur.php?controleur=controleurTheme&action=afficherThemes&idE=".htmlspecialchars($idE)."'>Retour</a>";
} else {
    echo "<a href='routeur.php?controleur=controleurGroupe&action=lireGroupesUtilisateur'>Retour</a>";
}

if (!$signalements) {
    echo "<p>Aucun signalement pour ce groupe.</p>";
} else {
    foreach ($signalements as $signalement) {
        $signalement->afficher();
    }
}
?>

