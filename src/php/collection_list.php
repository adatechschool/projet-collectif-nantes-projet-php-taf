<?php
session_start();

/* -------------------------------- */
// On vérifie que l'utilisateur est connecté sinon on redirige vers la page de connexion
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit();
}
/* ==================================== */

require 'config.php';

try {
    /* --------------------------- */
    $limit = 3;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    /* ==================================== */

    /* -------------------------------- */
    // On récupère les informations sur les collectes, les bénévoles ayant participé aux collectes et les types de déchets collectés et leurs quantités
    // La fonction MySQL GROUP_CONCAT permet:
    // 1. de regrouper les noms des bénévoles associés à une collecte dans une chaîne de caractères unique, en les séparant par une virgule
    // 2. assembler les détails des déchets collectés (type et quantité) en une seule chaîne pour chaque collecte.
    // La fonction CONCAT permet de réunir les types et quantités de déchets.
    $sqlQuery = "SELECT benevoles_collectes.id_collecte as id, collectes.date_collecte, collectes.lieu,
                    GROUP_CONCAT(DISTINCT benevoles.nom ORDER BY benevoles.nom SEPARATOR ', ') AS benevoles,
                    GROUP_CONCAT(DISTINCT CONCAT(COALESCE(dechets_collectes.type_dechet, 'type(s) non défini(s)'), ' (', ROUND(COALESCE(dechets_collectes.quantite_kg, 0), 1), 'kg)') ORDER BY dechets_collectes.type_dechet SEPARATOR ', ') AS wasteDetails
                FROM benevoles
                INNER JOIN benevoles_collectes ON benevoles.id = benevoles_collectes.id_benevole
                INNER JOIN collectes ON collectes.id = benevoles_collectes.id_collecte
                LEFT JOIN dechets_collectes ON collectes.id = dechets_collectes.id_collecte
                GROUP BY benevoles_collectes.id_collecte
                ORDER BY collectes.date_collecte DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sqlQuery);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $collectes = $stmt->fetchAll();
    /* ==================================== */

    /* ------------------------------ */
    // Requête pour récupérer le nombre total de collectes (pour la pagination et le tableau de bord)
    $sqlCount = "SELECT COUNT(DISTINCT benevoles_collectes.id_collecte) AS total
                 FROM benevoles
                 INNER JOIN benevoles_collectes ON benevoles.id = benevoles_collectes.id_benevole
                 INNER JOIN collectes ON collectes.id = benevoles_collectes.id_collecte";
    $totalStmt = $pdo->query($sqlCount);
    $totalCollectes = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalCollectes / $limit);
    /* ------------------------------ */

    /* ------------------------------ */
    // Requête pour récupérer la dernière collecte (pour le tableau de bord)
    $sqlLatest = "SELECT lieu, date_collecte FROM collectes ORDER BY date_collecte DESC LIMIT 1";
    $stmtLatest = $pdo->query($sqlLatest);
    $latestCollecte = $stmtLatest->fetch();
    /* ------------------------------ */

    /* -------------------------------- */
    // On récupère le total des déchets collectés pour l'ensemble des collectes réalisées
    $sqlQuery2 = "SELECT ROUND(SUM(COALESCE(dechets_collectes.quantite_kg,0)),1)
        AS quantite_total_des_dechets_collectes
        FROM collectes
        LEFT JOIN dechets_collectes ON collectes.id=dechets_collectes.id_collecte";
    $stmt2 = $pdo->query($sqlQuery2);
    $quantite = $stmt2->fetch();
    /* ==================================== */

    /* -------------------------------- */
    // On récupère le nom du premier bénévole admin
    $sqlQuery3 = "SELECT nom FROM benevoles WHERE role = 'admin' LIMIT 1";
    $query = $pdo->prepare($sqlQuery3);
    $query->execute();
    $admin = $query->fetch();
    $adminNom = $admin ? htmlspecialchars($admin['nom']) : 'Aucun administrateur trouvé';
    /* ==================================== */
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
    <!-- <head>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Lora:wght@400;700&family=Montserrat:wght@300;400;700&family=Open+Sans:wght@300;400;700&family=Poppins:wght@300;400;700&family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;700&family=Nunito:wght@300;400;700&family=Merriweather:wght@300;400;700&family=Oswald:wght@300;400;700&display=swap" rel="stylesheet">
    </head> -->
    <?php require 'headElement.php'; ?>
    <title>Liste des Collectes</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">
        <?php require 'navbar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <header>
                <h1 class="text-4xl font-bold text-cyan-950 mb-6">Liste des Collectes de Déchets</h1>
            </header>

            <!-- Message de notification (ex: succès de suppression ou ajout) -->
            <?php if (isset($_GET['message'])): ?>
                <aside role="alert" class="bg-green-100 text-green-800 p-4 rounded-md mb-6">
                    <?= htmlspecialchars($_GET['message']) ?>
                </aside>
            <?php endif; ?>

            <!-- Cartes d'informations -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Nombre total de collectes -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Total des Collectes</h3>
                    <p class="text-3xl font-bold text-blue-600"><?= count($collectes) ?></p>
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
            </section>
            <!-- Tableau des collectes -->
            <div class="overflow-hidden rounded-lg shadow-lg bg-white">
                <table class="w-full table-auto border-collapse">
                    <thead class="bg-cyan-950 text-white">
                        <tr>
                            <th scop="col" class="py-3 px-4 text-left">Date</th>
                            <th scop="col" class="py-3 px-4 text-left">Lieu</th>
                            <th scop="col" class="py-3 px-4 text-left">Bénévoles</th>
                            <th scop="col" class="py-3 px-4 text-left">Collectes</th>
                            <?php if ($_SESSION["role"] !== "admin"): ?>
                            <?php else: ?>
                                <th scop="col" class="py-3 px-4 text-left">Actions</th>
                            <?php endif ?>
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
                                <?php if ($_SESSION["role"] !== "admin"): ?>
                                <?php else: ?>
                                    <td class="py-3 px-4 flex space-x-2">
                                        <a href="collection_edit.php?id=<?= $collecte['id'] ?>" class="bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" aria-label="Modifier la collecte <?= htmlspecialchars($collecte['benevoles']) ?>" role="button"
                                            title="Modifier la collecte <?= htmlspecialchars($collecte['benevoles']) ?>">
                                            Modifier
                                        </a>

                                        <a href="collection_delete.php?id=<?= $collecte['id'] ?>"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200" aria-label="supprimer une collecte"
                                            role="button"
                                            title="supprimer une collecte"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette collecte ?');">
                                            Supprimer
                                        </a>
                                    </td>
                                <?php endif ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Contrôles de pagination -->
            <div class="flex justify-center items-center space-x-4 mt-4">
                <!-- Bouton Précédent -->
                <a href="?page=<?= max(1, $page - 1) ?>"
                    class="min-w-[120px] text-center bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md transition <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                    Précédent
                </a>

                <span class="text-gray-700 font-semibold">Page <?= $page ?> sur <?= $totalPages ?></span>

                <!-- Bouton Suivant -->
                <a href="?page=<?= min($totalPages, $page + 1) ?>"
                    class="min-w-[120px] text-center bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md transition <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                    Suivant
                </a>
            </div>
        </main>
    </div>
</body>

</html>