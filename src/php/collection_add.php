<?php
session_start();
if(!isset($_SESSION["user_id"])){
    header('Location: login.php');
    exit();
}

require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Récupérer la liste des bénévoles
$stmt_benevoles = $pdo->query("SELECT id, nom FROM benevoles ORDER BY nom");
$stmt_benevoles->execute();
$benevoles = $stmt_benevoles->fetchAll();

$stmtWasteTypes = $pdo->query("SELECT DISTINCT type_dechet FROM dechets_collectes");
$wasteTypes = $stmtWasteTypes->fetchAll(PDO::FETCH_COLUMN);

$options = "<option value=''>Sélectionner un type</option>";
foreach ($wasteTypes as $wasteType) {
    $options .= "<option value='" . htmlspecialchars($wasteType) . "'>" . htmlspecialchars($wasteType) . "</option>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"];
    $lieu = $_POST["lieu"];
    $benevoles_ids = isset($_POST["benevoles"]) ? $_POST["benevoles"] : [];

    // Insérer la collecte avec le bénévole sélectionné
    $stmt = $pdo->prepare("INSERT INTO collectes (date_collecte, lieu) VALUES (?, ?)");
    if (!$stmt->execute([$date, $lieu])) {
        die('Erreur lors de l\'insertion dans la base de données.');
    }

    $collecte_id = $pdo->lastInsertId();

    $stmtBc = $pdo->prepare("INSERT INTO benevoles_collectes (id_collecte, id_benevole) VALUES (?, ?)");
    foreach ($benevoles_ids as $benevole_id) {
        if (!$stmtBc->execute([$collecte_id, $benevole_id])) {
            die('Erreur lors de l\'insertion dans la base de données.');
        }
    }

    if (isset($_POST['type_dechet']) && isset($_POST['quantite_kg'])) {
        $stmtWaste = $pdo->prepare("INSERT INTO dechets_collectes (id_collecte, type_dechet, quantite_kg) VALUES (?, ?, ?)");
        $types = $_POST['type_dechet'];
        $quantities = $_POST['quantite_kg'];
        for ($i = 0; $i < count($types); $i++) {
            // On insère uniquement si le type n'est pas vide et la quantité est numérique
            if (!empty($types[$i]) && is_numeric($quantities[$i])) {
                $stmtWaste->execute([$collecte_id, $types[$i], $quantities[$i]]);
            }
        }
    }

    header("Location: collection_list.php");
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
    <title>Ajouter une collecte</title>
</head>

<body class="bg-gray-100 text-gray-900">

    <div class="flex h-screen">
        <!-- Barre de navigation -->
        <?php require 'navbar.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- Titre -->
            <h1 class="text-4xl font-bold mb-6">Ajouter une collecte</h1>

            <!-- Formulaire -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form method="POST" class="space-y-4">
                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Date :
                            <input type="date" name="date" required
                                class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </label>
                    </div>

                    <!-- Lieu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Lieu :
                            <input type="text" name="lieu" required
                                class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </label>
                    </div>

                    <!-- Bénévoles -->
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-2">Bénévoles :</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <?php foreach ($benevoles as $benevole): ?>
                                <div class="flex items-center">
                                    <input type="checkbox" name="benevoles[]" value="<?= $benevole['id'] ?>" id="benevole_<?= $benevole['id'] ?>" class="mr-2">
                                    <label for="benevole_<?= $benevole['id'] ?>" class="text-gray-700">
                                        <?= htmlspecialchars($benevole['nom']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Déchets collectés section -->
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-2">Déchets collectés :</span>
                        <div id="waste-container">
                            <div class="waste-item flex space-x-4 mb-2">
                                <select name="type_dechet[]" required class="w-full p-2 border border-gray-300 rounded-lg">
                                    <?= $options ?>
                                </select>
                                <input type="number" step="any" name="quantite_kg[]" placeholder="Quantité (kg)" class="w-full p-2 border border-gray-300 rounded-lg" required>
                                <button type="button" class="bg-cyan-950 remove-waste bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                        <button type="button" id="add-waste" class="bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg mt-2">
                            Ajouter un déchet
                        </button>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end space-x-4">
                        <a href="collection_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">Annuler</a>
                        <button type="submit" class="bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>

    </div>
    </div>
    <script>
        const wasteRowTemplate = `
            <div class="waste-item flex space-x-4 mb-2">
                <select name="type_dechet[]" required class="w-full p-2 border border-gray-300 rounded-lg">
                    <?= $options ?>
                </select>
                <input type="number" step="any" name="quantite_kg[]" placeholder="Quantité (kg)" class="w-full p-2 border border-gray-300 rounded-lg" required>
                <button type="button" class="remove-waste bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                    Supprimer
                </button>
            </div>
        `;

        document.getElementById('add-waste').addEventListener('click', function() {
            const container = document.getElementById('waste-container');
            container.insertAdjacentHTML('beforeend', wasteRowTemplate);
        });

        // Handle removal of a waste row
        document.getElementById('waste-container').addEventListener('click', function(e) {
            if (e.target && e.target.matches('button.remove-waste')) {
                e.target.parentNode.remove();
            }
        });
    </script>
</body>

</html>