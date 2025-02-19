<?php
session_start();

/* ------------------------------------- */
// On vérifie que l'utilisateur·trice est connecté·e et qu'il·elle est un·e admin
// Dans le cas contraire, on redirige vers la page de connexion ou la page de liste des collectes
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: collection_list.php");
    exit();
}
/* ========================================== */

require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ------------------------------------- */
// On redirige vers la liste des collectes si l'utilisateur·trice à modifier n'existe pas ou n'a pas été récupéré·e correctement.
$dashboardRedirection = "Location: collection_list.php";
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header($dashboardRedirection);
    exit;
}
/* ========================================== */

/* ------------------------------------- */
// On récupère les informations sur la collecte à modifier
// Et si la collecte n'existe pas ou n'a pas été récupéré·e correctement, on redirige vers la liste des collectes
$statement = $pdo->prepare("SELECT id, date_collecte, lieu FROM collectes WHERE id = ?");
$id = $_GET['id'];
$statement->execute([$id]);
$collecte = $statement->fetch();
if (!$collecte) {
    header($dashboardRedirection);
    exit;
}
/* ========================================== */

/* ------------------------------------- */
// On récupère les ids des bénévoles associés à la collecte
$stmtBc = $pdo->prepare("SELECT id_benevole FROM benevoles_collectes WHERE id_collecte = ?");
$stmtBc->execute([$id]);
$selectedBenevoles = $stmtBc->fetchAll(PDO::FETCH_COLUMN);
/* ========================================== */

/* ------------------------------------- */
// Récupérer la liste des bénévoles
$stmt_benevoles = $pdo->prepare("SELECT id, nom FROM benevoles ORDER BY nom");
$stmt_benevoles->execute();
$benevoles = $stmt_benevoles->fetchAll();
/* ========================================== */

/* ------------------------------------- */
// On récupère la liste des types et quantités de déchets pour la collecte à modifier
$stmtWasteItems = $pdo->prepare("SELECT type_dechet, quantite_kg FROM dechets_collectes WHERE id_collecte = ?");
$stmtWasteItems->execute([$id]);
$wasteItems = $stmtWasteItems->fetchAll();

$stmtWasteTypes = $pdo->query("SELECT DISTINCT type_dechet FROM dechets_collectes");
$wasteTypes = $stmtWasteTypes->fetchAll(PDO::FETCH_COLUMN);

if (empty($wasteItems)) {
    $wasteItems[] = ['type_dechet' => '', 'quantite_kg' => ''];
}
/* ========================================== */

/* ------------------------------------- */
// Lors de la soumission du formulaire...
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* ------------------------------------- */
    // Valeurs rentrées dans le champs du formulaire
    $date = $_POST["date"];
    $lieu = $_POST["lieu"];
    $benevoles_ids = isset($_POST['benevoles']) ? $_POST['benevoles'] : [];
    $type_de_dechet = $_POST["type_dechet"];
    $quantite_dechet = $_POST["quantite_kg"];
    /* ========================================== */

    /* ------------------------------------- */
    // On met à jour la date et le lieu de la collecte
    // La fonction COALESCE permet de ne pas mettre à jour les champs si leur valeur est vide
    $stmtUpdate = $pdo->prepare("UPDATE collectes SET date_collecte = COALESCE(?, date_collecte), lieu = COALESCE(?, lieu) WHERE id = ?");
    if (!$stmtUpdate->execute([$date, $lieu, $id])) {
        die('Erreur lors de la mise à jour de la collecte.');
    }
    /* ========================================== */

    /* ------------------------------------- */
    // On met à jour les bénévoles associés à la collecte et les types et quantités de déchets collectés
    // C'est des mises à jour qui se font en 2 étapes :
    // 1. On supprime les anciens bénévoles associés à la collecte
    // 2. On insère les nouveaux bénévoles associés à la collecte
    $stmtDeleteBc = $pdo->prepare("DELETE FROM benevoles_collectes WHERE id_collecte = ?");
    $stmtDeleteBc->execute([$id]);
    $stmtInsertBc = $pdo->prepare("INSERT INTO benevoles_collectes (id_collecte, id_benevole) VALUES (?, ?)");
    foreach ($benevoles_ids as $benevole_id) {
        if (!$stmtInsertBc->execute([$id, $benevole_id])) {
            die('Erreur lors de la mise à jour des bénévoles de la collecte.');
        }
    }

    $stmtDeleteWaste = $pdo->prepare("DELETE FROM dechets_collectes WHERE id_collecte = ?");
    $stmtDeleteWaste->execute([$id]);
    if (isset($_POST['type_dechet']) && isset($_POST['quantite_kg'])) {
        $stmtWaste = $pdo->prepare("INSERT INTO dechets_collectes (id_collecte, type_dechet, quantite_kg) VALUES (?, ?, ?)");
        $types = $_POST['type_dechet'];
        $quantities = $_POST['quantite_kg'];
        for ($i = 0; $i < count($types); $i++) {
            if (!empty($types[$i]) && is_numeric($quantities[$i])) {
                $stmtWaste->execute([$id, $types[$i], $quantities[$i]]);
            }
        }
    }
    /* ========================================== */

    header($dashboardRedirection);
    exit;
}
/* ========================================== */
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require 'headElement.php'; ?>
    <title>Modifier une collecte</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">
        <?php require 'navbar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold text-cyan-950 mb-6">Modifier une collecte</h1>

            <!-- Formulaire -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form method="POST" class="space-y-4">
                    <!-- champ date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Date :
                            <input type="date" name="date" value="<?= htmlspecialchars($collecte['date_collecte']) ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <!-- =============== -->

                    <!-- champ date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Lieu :
                            <input type="text" name="lieu" value="<?= htmlspecialchars($collecte['lieu']) ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <!-- =============== -->

                    <!-- champ bénévoles -->
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-2">Bénévoles :</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <?php foreach ($benevoles as $benevole): ?>
                                <div class="flex items-center">
                                    <input type="checkbox" name="benevoles[]" value="<?= $benevole['id'] ?>" id="benevole_<?= $benevole['id'] ?>" class="mr-2"
                                        <?= in_array($benevole['id'], $selectedBenevoles) ? 'checked' : '' ?>>
                                    <label for="benevole_<?= $benevole['id'] ?>" class="text-gray-700">
                                        <?= htmlspecialchars($benevole['nom']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- =============== -->

                    <!-- champ déchets collectés -->
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-2">Déchets collectés :</span>
                        <div id="waste-container">
                            <?php foreach ($wasteItems as $item):
                                // Construire les options pour le select avec le type déjà sélectionné
                                $selectOptions = "<option value=''>Sélectionner un type</option>";
                                foreach ($wasteTypes as $wasteType) {
                                    $selected = ($wasteType == $item['type_dechet']) ? 'selected' : '';
                                    $selectOptions .= "<option value='" . htmlspecialchars($wasteType) . "' $selected>" . htmlspecialchars($wasteType) . "</option>";
                                }
                            ?>
                                <div class="waste-item flex space-x-4 mb-2">
                                    <select name="type_dechet[]" class="w-full p-2 border border-gray-300 rounded-lg">
                                        <?= $selectOptions ?>
                                    </select>
                                    <input type="number" step="0.1" name="quantite_kg[]" placeholder="Quantité (kg)" value="<?= htmlspecialchars($item['quantite_kg']) ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                                    <button type="button" class="bg-cyan-950 remove-waste hover:bg-red-600 text-white px-2 py-1 rounded">
                                        Supprimer
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- =============== -->

                        <button type="button" id="add-waste" class="bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg mt-2">
                            Ajouter un déchet
                        </button>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <a href="collection_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">Annuler</a>
                        <button type="submit" class="bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow">
                            Modifier
                        </button>
                    </div>
                </form>
            </div>
    </div>
    </div>
    <script>
        /* ---------------------- */
        // Ce bout de code gère la création et la suppression dynamique des inputs types et quantités de déchets
        const wasteRowTemplate = `
        <div class="waste-item flex space-x-4 mb-2">
        <select name="type_dechet[]" class="w-full p-2 border border-gray-300 rounded-lg">
        <option value="">Sélectionner un type</option>
        <?php foreach ($wasteTypes as $wasteType): ?>
                        <option value="<?= htmlspecialchars($wasteType) ?>"><?= htmlspecialchars($wasteType) ?></option>
                    <?php endforeach; ?>
                    </select>
                    <input type="number" step="0.1" name="quantite_kg[]" placeholder="Quantité (kg)" class="w-full p-2 border border-gray-300 rounded-lg">
                <button type="button" class="bg-cyan-950 remove-waste hover:bg-red-600 text-white px-2 py-1 rounded">
                    Supprimer
                </button>
            </div>
            `;

        /**
         * Met à jour les listes déroulantes en désactivant les options déjà sélectionnées dans d'autres selects.
         */
        function updateWasteSelectOptions() {
            const selects = document.querySelectorAll("select[name='type_dechet[]']");
            const selectedValues = Array.from(selects)
                .map(select => select.value)
                .filter(value => value !== "");

            selects.forEach(select => {
                select.querySelectorAll("option").forEach(option => {
                    if (selectedValues.includes(option.value) && option.value !== select.value) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
            });
        }

        // Mettre à jour dès qu'un select change
        document.getElementById('waste-container').addEventListener('change', function(e) {
            if (e.target && e.target.matches("select[name='type_dechet[]']")) {
                updateWasteSelectOptions();
            }
        });

        // Ajout d'une nouvelle ligne
        document.getElementById('add-waste').addEventListener('click', function() {
            const container = document.getElementById('waste-container');
            container.insertAdjacentHTML('beforeend', wasteRowTemplate);
            updateWasteSelectOptions();
        });

        // Suppression d'une ligne et mise à jour des options
        document.getElementById('waste-container').addEventListener('click', function(e) {
            if (e.target && e.target.matches('button.remove-waste')) {
                e.target.parentNode.remove();
                updateWasteSelectOptions();
            }
        });

        // Appel initial pour désactiver les options déjà sélectionnées au chargement de la page
        updateWasteSelectOptions();
        /* ================================== */
    </script>
</body>

</html>