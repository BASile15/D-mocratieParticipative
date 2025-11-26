<section class="bodyForm">
	<section class="Connexion">
		<section class="imgEtTexteAGauche">
			<h2>MyVote</h2>
			<img title="imgLogoForm" src="images\logo_sans_fond.png"/>
			<p>La platforme de démocratie participative !</p>
		</section>

		<section class="SectionForm">
			<form class="formulaire" method="POST" action="routeur.php?controleur=controleurUtilisateur&action=connexion">
				<h1> 
					Se connecter
				</h1>

				<label for="email">Email :</label>
				<input type="email" name="email" required>
				
				<label for="password">Mot de passe :</label>
				<input type="password" name="password" required>
				
				<button type="submit">Se connecter</button>
				<a href="routeur.php?controleur=controleurUtilisateur&action=formInscription"> Pas encore de compte ? </a>
				<?php echo "<script src='js/popupErr.js'></script>"; ?>
			</form>
		</section>
	</section>
</section>