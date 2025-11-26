<h1 class="TitreH2">Thèmes du groupe</h1>

<?php
if (isset($isAdmin) && $isAdmin || isset($isMode) && $isMode) {
    echo "<a href='routeur.php?controleur=controleurTheme&action=formTheme' class='btn-signalements'>Ajouter un thème</a>";
}
if (empty($themes)) {
    echo "<p>Aucun thème disponible.</p>";
} else {
    foreach ($themes as $theme) {
        $theme->afficher();
    }
}
?>

<?php
if (isset($isAdmin) && $isAdmin || isset($isMode) && $isMode) {
    if (isset($idGroupe) && !empty($idGroupe)) {
        echo "<a href='routeur.php?controleur=controleurSignalement&action=afficherSignalements&idE=" . urlencode($idGroupe) . "' class='btn-signalements'>Voir les signalements</a>";
    } else {
        echo "<p>Erreur: ID du groupe non défini.</p>";
    }
}
?>
<button class='btn-retour' onclick="history.back()">Retour</button>
