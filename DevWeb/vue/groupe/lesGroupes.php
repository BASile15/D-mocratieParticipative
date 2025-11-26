<h2 class="TitreH2">Vos groupes</h2>

<?php
function hexToRgba($hex, $alpha = 0.5) {
    if (strlen($hex) == 7) {
        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));
    }
    return "rgba($r, $g, $b, $alpha)";
}

foreach ($groupes as $grp) {
    $couleurBackground = hexToRgba($grp['couleurGroupe'], 0.6);
    
    echo "<a class='lienAffichage' href='routeur.php?controleur=controleurTheme&action=afficherThemes&idE=".$grp["idGroupe"]."'>";
        echo "<section class='sectionAfficher' style='background-color: $couleurBackground;'>";
            echo "<section class='AffichageGroupeGauche'>";
                echo "<h3>".$grp["nomGroupe"]."</h3>";
                echo "<p>Description : ".$grp["descriptionGroupe"]."</p>";
            echo "</section>";

            echo "<section class='AffichageGroupeDroite'>";
                if (Groupe::getRoleDansGroupe($grp["idGroupe"]) === 2) {
                    echo "<p><a class='lienDansGroupe' href='routeur.php?controleur=controleurInvitation&action=AfficherFormulaireInvitation&idGroupe=".$grp["idGroupe"]."'>Inviter des utilisateurs à rejoindre ce groupe</a></p>";
                    echo "<p><a class='lienDansGroupe' href='routeur.php?controleur=controleurGroupe&action=lireUtilisateursDuGroupe&idE=".$grp["idGroupe"]."'>Voir les utilisateurs</a></p>";
                }
            echo "</section>";
        echo "</section>";
    echo "</a>";
}
?>
