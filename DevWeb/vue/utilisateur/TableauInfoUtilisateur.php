<h2 class="TitreH2"> Vos infos </h2>

<section class="tabEtLienUtilisateur">
    <table class="tabInfoUtilisateur">
         <tr>
            <th>Nom</th>
            <td><?php echo $infos['nom'];?></td>
        </tr>
         <tr>
            <th>Prénom</th>
            <td><?php echo $infos['prenom'];?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo $infos['email'];?></td>
        </tr>
        <tr>
            <th>Adresse</th>
            <td><?php echo $infos['adressePostale'];?></td>
        </tr>
        <tr>
            <th>Ville</th>
            <td><?php echo $infos['ville'];?></td>
        </tr>
    </table>
    <a href="routeur.php?controleur=controleurUtilisateur&action=formModifierInfoUtilisateur">Modifier mes informations</a>
    <br>
    <a href="routeur.php?controleur=controleurUtilisateur&action=deconnecterUtilisateur">Déconnexion</a>
</section>
