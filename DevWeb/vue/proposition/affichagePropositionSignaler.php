<h1>Propositions numéro : <?php echo $propositions->getIdProposition(); ?> Signaler par :</h1>

<?php
    $propositions->afficherSansSignaler(); 
?>
<button class='btn-retour' onclick="history.back()">Retour</button>

