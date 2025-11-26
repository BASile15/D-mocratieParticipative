<?php foreach ($commentaires as $commentaire): ?>
    <article class="commentaire">
        <p><strong>Utilisateur #<?= htmlspecialchars($commentaire->getIdUtilisateur()) ?> :</strong></p>
        <p><?= nl2br(htmlspecialchars($commentaire->getContenu())) ?></p>
        <p><em>Posté le <?= htmlspecialchars($commentaire->getDateCommentaire()) ?></em></p>

        <?php if (Utilisateur::aRoleDansGroupe($_SESSION['idUtilisateur'], $idGroupe, 3)): ?>
            <form action="routeur.php?controleur=controleurCommentaire&action=supprimerCommentaire" method="POST" style="display: inline;">
                <input type="hidden" name="idCommentaire" value="<?= htmlspecialchars($commentaire->getIdCommentaire()) ?>">
                <input type="hidden" name="idProposition" value="<?= htmlspecialchars($proposition->getIdProposition()) ?>">
                <input type="hidden" name="idGroupe" value="<?= htmlspecialchars($idGroupe) ?>">
                <button type="submit">Supprimer</button>
            </form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>

<h2 class="TitreH2">Commentaires</h2>

<?php
	foreach ($commentaires as $comm) {
		$comm->afficher();
	}
?>