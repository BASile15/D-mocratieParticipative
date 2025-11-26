<?php 
    $proposition->afficher();   
?>
<a href="routeur.php?controleur=controleurSignalement&action=supprimerSignalement&idE=<?php echo $proposition->getIdProposition(); ?>"> Approuver la proposition </a>
<a href="routeur.php?controleur=controleurSignalement&action=supprimerSignalementProposition&idE=<?php echo $proposition->getIdProposition(); ?>">Supprimer la proposition</a>
    