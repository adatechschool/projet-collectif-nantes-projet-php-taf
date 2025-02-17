<?php
require 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $statement = $pdo->query("SELECT benevoles.id, benevoles.nom, benevoles.email, benevoles.role, COALESCE(GROUP_CONCAT(CONCAT(collectes.lieu, ' (', collectes.date_collecte, ')') SEPARATOR ', '), 'Aucune participation pour le moment') AS 'participations' FROM benevoles LEFT JOIN benevoles_collectes ON benevoles.id = benevoles_collectes.id_benevole LEFT JOIN collectes ON collectes.id = benevoles_collectes.id_collecte GROUP BY benevoles.id ORDER BY benevoles.nom ASC");
    $volunteersList = $statement->fetchAll();
} catch (PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = "Liste des Bénévoles";
require 'headElement.php';
?>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">
        <!-- Barre de navigation -->
        <?php require 'navbar.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- Titre -->
            <h1 class="text-4xl font-bold mb-6">Liste des Bénévoles</h1>

            <!-- Tableau des bénévoles -->
            <div class="overflow-hidden rounded-lg shadow-lg bg-white">
                <table class="w-full table-auto border-collapse">
                    <thead class="text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Nom</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Rôle</th>
                            <th class="py-2 px-4 border-b">Collections</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        <?php for ($index = 0; $index < count($volunteersList); $index++): ?>
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["nom"]) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["email"]) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["role"]) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["participations"]) ? htmlspecialchars($volunteersList[$index]["participations"]) : "Aucune" ?></td>
                                <td class="py-3 px-4">
                                    <?php
                                    $editUrl = "volunteer_edit.php?id=" . urlencode($volunteersList[$index]["id"]);
                                    $deleteUrl = "volunteer_delete.php?id=" . urlencode($volunteersList[$index]["id"]);
                                    $confirmMessage = "Êtes-vous sûr de vouloir supprimer ce bénévole ?";
                                    require 'action_buttons.php';
                                    ?>
                                </td>
                            </tr>

                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
    </div>
    </div>
</body>

</html>