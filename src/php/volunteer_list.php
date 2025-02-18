<?php
session_start();
if(!isset($_SESSION["user_id"])){
    header('Location: login.php');
    exit();
}

require 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // récupération de la liste des bénévoles
    $statement = $pdo->prepare("SELECT benevoles.id, benevoles.nom, benevoles.email, benevoles.role, COALESCE(GROUP_CONCAT(CONCAT(collectes.lieu, ' (', collectes.date_collecte, ')') SEPARATOR ', '), 'Aucune participation pour le moment') AS 'participations' FROM benevoles LEFT JOIN benevoles_collectes ON benevoles.id = benevoles_collectes.id_benevole LEFT JOIN collectes ON collectes.id = benevoles_collectes.id_collecte GROUP BY benevoles.id ORDER BY benevoles.nom ASC LIMIT :limit OFFSET :offset"); // écriture de la requête

    // Sécurisation des variables dans la requête
    $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
    $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $volunteersList = $statement->fetchAll(); // exécution de la requête

    // Récupérer le nombre total de bénévoles (pour la pagination)
    $totalStmt = $pdo->query("SELECT COUNT(*) AS total FROM benevoles");
    $totalBenevoles = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalBenevoles / $limit);
} catch (PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/projet-collectif-nantes-projet-php-taf/src/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <title>Liste des Bénévoles</title>
</head>

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
                            <th class="py-2 px-4 border-b">Collectes</th>
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
                                <td class="py-3 px-4 flex space-x-2">
                                    <a href="volunteer_edit.php?id=<?= $volunteersList[$index]["id"] ?>"
                                        class="bg-cyan-200 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                                        ✏️ Modifier
                                    </a>
                                    <a href="volunteer_delete.php?id=<?= $volunteersList[$index]["id"] ?>"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none         focus:ring-2 focus:ring-red-500 transition duration-200">
                                        🗑️ Supprimer
                                    </a>
                                </td>
                            </tr>

                            <!-- syntaxe de fermeture d'une boucle -->
                        <?php endfor; ?>
                    </tbody>
                </table>

                <div class="flex justify-center items-center space-x-4 mt-4">
                    <!-- Bouton Précédent -->
                    <a href="?page=<?= max(1, $page - 1) ?>"
                        class="min-w-[120px] text-center bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow-md transition
                        <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                        ⬅️ Précédent
                    </a>

                    <span class="text-gray-700 font-semibold">Page <?= $page ?> sur <?= $totalPages ?></span>

                    <!-- Bouton Suivant -->
                    <a href="?page=<?= min($totalPages, $page + 1) ?>"
                        class="min-w-[120px] text-center bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow-md transition
                        <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                        Suivant ➡️
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>

</html>