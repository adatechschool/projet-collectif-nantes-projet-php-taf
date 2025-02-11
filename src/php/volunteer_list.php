<?php
// permet de récupérer les paramètres de connexion à la base de données
require 'config.php';

try {
    // récupération de la liste des bénévoles
    $statement = $pdo->query("SELECT * FROM benevoles"); // écriture de la requête
    $volunteersList = $statement->fetchAll(); // exécution de la requête
} catch(PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage();
    exit;
}

// active l'affichage des erreurs les rendant visible sur la page (à ne pas activer en production)
ini_set('display_errors', 1);

// active l'affichage des erreurs qui se produisent au démarrage de php (à ne pas activer en production)
ini_set('display_startup_errors', 1);

// définit les niveaux d'erreurs qui seront affichés (par exemple : E_ALL = tous les erreurs, E_ERROR = erreurs seulement, etc.) (à ne pas activer en production)
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
                <!-- thead est un élément de tableau qui permet de créer un titre pour le tableau -->
                <thead class="bg-blue-800 text-white">
                    <!-- tr est un élément de tableau qui permet de créer une ligne dans le tableau -->
                    <tr>
                        <!-- th est un élément de tableau qui permet de créer une case titre dans le tableau -->
                        <th class="py-3 px-4 text-left">Nom</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-left">Rôle</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <!-- tbody est un élément de tableau qui permet de regrouper les lignes contenant les données -->
                <tbody class="divide-y divide-gray-300">
                    <!-- on définit une boucle qui parcourt toutes les données de la liste des bénévoles -->
                    <?php for($index = 0; $index < count($volunteersList); $index++): ?>
                    <tr class="hover:bg-gray-100 transition duration-200">
                        <!-- td est un élément de tableau qui permet de créer une case contenant de la donnée en lien avec le   titre-->
                        <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["nom"]) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["email"]) ?></td>
                        <!-- htmlspecialchars permet de sécuriser les données qui sont affichées dans le tableau. Il convertit les caractères spéciaux HTML en entités HTML -->
                        <td class="py-3 px-4"><?= htmlspecialchars($volunteersList[$index]["role"]) ?></td>
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
        </div>
    </div>
</div>
</body>
</html>

