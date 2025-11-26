<h2>Groupes de l'utilisateur</h2>
<ul>
    <?php foreach ($groupes as $groupe) { ?>
        <li><?= htmlspecialchars($groupe['nom']); ?></li>
    <?php } ?>
</ul>
