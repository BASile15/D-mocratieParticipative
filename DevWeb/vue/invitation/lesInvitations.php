<h2 class="TitreH2">Vos notifications</h2>

<?php
	foreach ($invitations as $inv) {
		$inv->afficher();
	}
?>