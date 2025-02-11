<?php
require 'config.php';

try {
$statement = $pdo->query("SELECT * FROM benevoles");
$volunteersList = $statement->fetchAll();
} catch(PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage();
    exit;
}
// echo "<pre>";
// var_dump($volunteersList);
// echo "</pre>";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Bénévoles</title>
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
        <h1 class="text-4xl font-bold text-blue-800 mb-6">Liste des Bénévoles</h1>

        <!-- Tableau des bénévoles -->
        <div class="overflow-hidden rounded-lg shadow-lg bg-white">
            <table class="w-full table-auto border-collapse">
                <thead class="bg-blue-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Nom</th>
                    <th class="py-3 px-4 text-left">Email</th>
                    <th class="py-3 px-4 text-left">Rôle</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    <?php for($index = 0; $index < count($volunteersList); $index++): ?>
                <tr class="hover:bg-gray-100 transition duration-200">
                        
                <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["nom"]) ?></td>
                <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["email"]) ?></td>
                <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["role"]) ?></td>
                <td class="py-3 px-4 flex space-x-2">
                   <a href="volunteer_edit.php?id=<?= $volunteersList[$index]["id"] ?>"
                    class="bg-cyan-200 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                    ✏️ Modifier
                </a>
                <a href="volunteer_delete.php?id=<?= $volunteersList[$index]["id"] ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200">
                🗑️ Supprimer
            </a>
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

