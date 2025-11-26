<section class="SectionGererUtilisateurGroupe">
    <table class="tableUtilisateurs">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Expulser</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['prenom'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                    <td>
                        <form action="routeur.php?controleur=controleurRole&action=changerRoleUtilisateur" method="post" class="form-modifier-role">
                            <input type="hidden" name="idUtilisateur" value="<?= htmlspecialchars($user['idUtilisateur']) ?>">
                            <input type="hidden" name="idGroupe" value="<?= htmlspecialchars($idGroupe) ?>">
                            <select name="idRole" class="select-role">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= htmlspecialchars($role->getIdRole()) ?>" 
                                        <?= $role->getIdRole() == $user['idRole'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role->getNomRole()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-modifier">Modifier</button>
                        </form>
                    </td>
                    <td>
                        <form action="routeur.php?controleur=controleurGroupe&action=expulserUtilisateur" method="post" class="form-expulser">
                            <input type="hidden" name="idUtilisateur" value="<?= htmlspecialchars($user['idUtilisateur']) ?>">
                            <input type="hidden" name="idGroupe" value="<?= htmlspecialchars($idGroupe) ?>">
                            <button type="submit" class="btn-expulser">Expulser</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
