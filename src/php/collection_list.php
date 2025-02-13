<?php
require 'config.php';

try {
    $stmt = $pdo->query("SELECT collectes.id, collectes.date_collecte, IFNULL(benevoles.nom, 'Aucun bénévole') AS nom, collectes.lieu, GROUP_CONCAT(CONCAT(dechets_collectes.type_dechet, ' (', dechets_collectes.quantite_kg, 'kg)') SEPARATOR ', ') AS liste_types_dechet FROM dechets_collectes JOIN collectes ON dechets_collectes.id_collecte = collectes.id LEFT JOIN benevoles ON collectes.id_benevole = benevoles.id GROUP BY collectes.id ORDER BY collectes.date_collecte DESC");
    $collectes = $stmt->fetchAll();

    $stmt2 = $pdo->query("
        SELECT ROUND(SUM(COALESCE(dechets_collectes.quantite_kg,0)),1)
        AS quantite_total_des_dechets_collectes
        FROM collectes
        LEFT JOIN dechets_collectes ON collectes.id=dechets_collectes.id_collecte
    ");
    
    $quantite = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    $query = $pdo->prepare("SELECT nom FROM benevoles WHERE role = 'admin' LIMIT 1");
    $query->execute();
    $admin = $query->fetch(PDO::FETCH_ASSOC);
    $adminNom = $admin ? htmlspecialchars($admin['nom']) : 'Aucun administrateur trouvé';
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Collectes</title>
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Lora:wght@400;700&family=Montserrat:wght@300;400;700&family=Open+Sans:wght@300;400;700&family=Poppins:wght@300;400;700&family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;700&family=Nunito:wght@300;400;700&family=Merriweather:wght@300;400;700&family=Oswald:wght@300;400;700&display=swap" rel="stylesheet">
    </head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">
<div class="flex h-screen">
    <!-- Barre de navigation -->
     <?php require 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="flex-1 p-8 overflow-y-auto">
        <!-- Titre -->
        <h1 class="text-4xl font-bold text-blue-800 mb-6">Liste des Collectes de Déchets</h1>

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
                <thead class="bg-blue-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Date</th>
                    <th class="py-3 px-4 text-left">Lieu</th>
                    <th class="py-3 px-4 text-left">Bénévole Responsable</th>
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
                            <?= $collecte['nom'] ? htmlspecialchars($collecte['nom']) : 'Aucun bénévole' ?>
                        </td>
                        <td class="py-3 px-4"><?= htmlspecialchars($collecte['liste_types_dechet']) ?></td>
                        <td class="py-3 px-4 flex space-x-2">
                            <a href="collection_edit.php?id=<?= $collecte['id'] ?>" class="bg-cyan-200 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                                ✏️ Modifier
                            </a>
                            <a href="collection_delete.php?id=<?= $collecte['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette collecte ?');">
                                🗑️ Supprimer
                            </a>
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
