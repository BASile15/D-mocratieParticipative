<?php
echo"<h1 class='TitreH2'>Proposez une de vos idées en rapport avec ce themes !</h1>";
echo "<a href='routeur.php?controleur=controleurProposition&action=formProposition' class='btn-signalements'>Ajouter une proposition</a>";
    foreach ($propositions as $proposition) {
        $proposition->afficher();
    }
?>
<button class='btn-retour' onclick="history.back()">Retour</button>

