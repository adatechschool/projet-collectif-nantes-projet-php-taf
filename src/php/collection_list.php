<?php
require 'config.php';

try {
    $stmt = $pdo->query("SELECT bc.id_collecte as id, c.date_collecte, c.lieu,
                    GROUP_CONCAT(DISTINCT v.nom ORDER BY v.nom SEPARATOR ', ') AS benevoles,
                    GROUP_CONCAT(DISTINCT CONCAT(COALESCE(dc.type_dechet, 'type non défini'), ' (', ROUND(COALESCE(dc.quantite_kg, 0), 1), 'kg)') ORDER BY dc.type_dechet SEPARATOR ', ') AS wasteDetails
                FROM benevoles v
                INNER JOIN benevoles_collectes bc ON v.id = bc.id_benevole
                INNER JOIN collectes c ON c.id = bc.id_collecte
                LEFT JOIN dechets_collectes dc ON c.id = dc.id_collecte
                GROUP BY bc.id_collecte
                ORDER BY c.date_collecte DESC");
    $collectes = $stmt->fetchAll();

    $query = $pdo->prepare("SELECT nom FROM benevoles WHERE role = 'admin' LIMIT 1");
    $query->execute();
    $admin = $query->fetch(PDO::FETCH_ASSOC);
    $adminNom = $admin ? htmlspecialchars($admin['nom']) : 'Aucun administrateur trouvé';

    $stmt2 = $pdo->query("
        SELECT ROUND(SUM(COALESCE(dechets_collectes.quantite_kg,0)),1)
        AS quantite_total_des_dechets_collectes
        FROM collectes
        LEFT JOIN dechets_collectes ON collectes.id=dechets_collectes.id_collecte
    ");

    $quantite = $stmt2->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage();
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = "Liste des Collectes";
require 'headElement.php';
?>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">
        <!-- Barre de navigation -->
        <?php require 'navbar.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- Titre -->
            <h1 class="text-4xl font-bold mb-6">Liste des Collectes de Déchets</h1>

            <!-- Message de notification (ex: succès de suppression ou ajout) -->
            <?php if (isset($_GET['message'])): ?>
                <div class="bg-green-100 text-green-800 p-4 rounded-md mb-6">
                    <?= htmlspecialchars($_GET['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Cartes d'informations -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Nombre total de collectes -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Total des Collectes</h3>
                    <p class="text-3xl font-bold text-blue-600"><?= htmlspecialchars(count($collectes)) ?></p>
                </div>
                <!-- Dernière collecte -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Dernière Collecte</h3>
                    <p class="text-lg text-gray-600"><?= htmlspecialchars($collectes[0]['lieu']) ?></p>
                    <p class="text-lg text-gray-600"><?= date('d/m/Y', strtotime($collectes[0]['date_collecte'])) ?></p>
                </div>
                <!-- quantité total déchet de la collecte -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Total des déchets collecté </h3>
                    <p class="text-3xl font-bold text-blue-600"><?= $quantite['quantite_total_des_dechets_collectes'] ?> kg</p>
                </div>
                <!-- Bénévole Responsable -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Bénévole Admin</h3>
                    <p class="text-lg text-gray-600"><?= $adminNom ?></p>
                </div>
            </div>

            <!-- Tableau des collectes -->
            <div class="overflow-hidden rounded-lg shadow-lg bg-white">
                <table class="w-full table-auto border-collapse">
                    <thead class="text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Lieu</th>
                            <th class="py-3 px-4 text-left">Bénévoles</th>
                            <th class="py-3 px-4 text-left">Type de déchets (quantité par type en kg)</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        <?php foreach ($collectes as $collecte) : ?>
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <td class="py-3 px-4"><?= date('d/m/Y', strtotime($collecte['date_collecte'])) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($collecte['lieu']) ?></td>
                                <td class="py-3 px-4">
                                    <?= $collecte['benevoles'] ? htmlspecialchars($collecte['benevoles']) : 'Aucun bénévole' ?>
                                </td>
                                <td class="py-3 px-4"><?= htmlspecialchars($collecte['wasteDetails']) ?></td>
                                <td class="py-3 px-4">
                                    <?php
                                    $editUrl = "collection_edit.php?id=" . urlencode($collecte['id']);
                                    $deleteUrl = "collection_delete.php?id=" . urlencode($collecte['id']);
                                    $confirmMessage = "Êtes-vous sûr de vouloir supprimer cette collecte ?";
                                    require 'action_buttons.php';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </div>
    </div>
</body>

</html>