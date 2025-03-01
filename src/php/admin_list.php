<?php
// permet de récupérer les paramètres de connexion à la base de données
require 'config.php';

try {
    // récupération de la liste des bénévoles
    $statement = $pdo->query("SELECT benevoles.id, benevoles.nom, benevoles.email, benevoles.role, ROUND(COALESCE(SUM(dechets_collectes.quantite_kg), 0), 1) AS quantite_totale_dechets_kg FROM benevoles LEFT JOIN collectes ON benevoles.id = collectes.id_benevole LEFT JOIN dechets_collectes ON collectes.id = dechets_collectes.id_collecte WHERE benevoles.role = 'admin' GROUP BY benevoles.id, benevoles.nom, benevoles.email, benevoles.role"); // écriture de la requête
    $adminsList = $statement->fetchAll(); // exécution de la requête
} catch (PDOException $e) {
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

<?php
$pageTitle = "Liste des Admins";
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

            <!-- Tableau des admin -->
            <div class="overflow-hidden rounded-lg shadow-lg bg-white">
                <table class="w-full table-auto border-collapse">
                    <thead class="text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Nom</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Rôle</th>
                            <th class="py-3 px-4 text-left">Quantité totale des déchets collectés (kg)</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        <?php for ($index = 0; $index < count($adminsList); $index++): ?>
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <!-- td est un élément de tableau qui permet de créer une case contenant de la donnée en lien avec le   titre-->
                                <td class="py-3 px-4"><?= htmlspecialchars($adminsList[$index]["nom"]) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($adminsList[$index]["email"]) ?></td>
                                <!-- htmlspecialchars permet de sécuriser les données qui sont affichées dans le tableau. Il convertit les caractères spéciaux HTML en entités HTML -->
                                <td class="py-3 px-4"><?= htmlspecialchars($adminsList[$index]["role"]) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($adminsList[$index]["quantite_totale_dechets_kg"]) ?></td>
                                <td class="py-3 px-4 flex space-x-2">
                                    <?php
                                    // Even if the edit/delete pages are the same as volunteers, the context might be different.
                                    $editUrl = "volunteer_edit.php?id=" . urlencode($adminsList[$index]["id"]);
                                    $deleteUrl = "volunteer_delete.php?id=" . urlencode($adminsList[$index]["id"]);
                                    $confirmMessage = "Êtes-vous sûr de vouloir supprimer cet administrateur ?";
                                    include 'action_buttons.php';
                                    ?>
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