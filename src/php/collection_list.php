<?php
session_start();
if(!isset($_SESSION["user_id"])){
    header('Location: login.php');
    exit();
}

require 'config.php';

try {

    $limit = 3;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->query("SELECT bc.id_collecte as id, c.date_collecte, c.lieu,
                    GROUP_CONCAT(DISTINCT v.nom ORDER BY v.nom SEPARATOR ', ') AS benevoles,
                    GROUP_CONCAT(DISTINCT CONCAT(COALESCE(dc.type_dechet, 'type(s) non défini(s)'), ' (', ROUND(COALESCE(dc.quantite_kg, 0), 1), 'kg)') ORDER BY dc.type_dechet SEPARATOR ', ') AS wasteDetails
                FROM benevoles v
                INNER JOIN benevoles_collectes bc ON v.id = bc.id_benevole
                INNER JOIN collectes c ON c.id = bc.id_collecte
                LEFT JOIN dechets_collectes dc ON c.id = dc.id_collecte
                GROUP BY bc.id_collecte
                ORDER BY c.date_collecte DESC
                LIMIT :limit OFFSET :offset");

    $collectes = $stmt->fetchAll();

    $totalStmt = $pdo->query("SELECT COUNT(*) AS total FROM collectes");
    $totalCollectes = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalCollectes / $limit);


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
    <!-- <head>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Lora:wght@400;700&family=Montserrat:wght@300;400;700&family=Open+Sans:wght@300;400;700&family=Poppins:wght@300;400;700&family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;700&family=Nunito:wght@300;400;700&family=Merriweather:wght@300;400;700&family=Oswald:wght@300;400;700&display=swap" rel="stylesheet">
    </head> -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/projet-collectif-nantes-projet-php-taf/src/css/style.css">
    <title>Liste des Collectes</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">
        <!-- Barre de navigation -->

        <?php require 'navbar.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- Titre -->
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
                            <?php if($_SESSION["role"] !== "admin"): ?>
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
                                <?php if($_SESSION["role"] !== "admin"): ?>
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
        </main>
    </div>
</body>

</html>