<?php
require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$dashboardRedirection = "Location: collection_list.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header($dashboardRedirection);
    exit;
}

$id = $_GET['id'];

// Récupérer les informations de la collecte
$stmt = $pdo->prepare("SELECT * FROM collectes WHERE id = ?");
$stmt->execute([$id]);
$collecte = $stmt->fetch();

if (!$collecte) {
    header($dashboardRedirection);
    exit;
}

$stmtBc = $pdo->prepare("SELECT id_benevole FROM benevoles_collectes WHERE id_collecte = ?");
$stmtBc->execute([$id]);
$selectedBenevoles = $stmtBc->fetchAll(PDO::FETCH_COLUMN);

// Récupérer la liste des bénévoles
$stmt_benevoles = $pdo->prepare("SELECT id, nom FROM benevoles ORDER BY nom");
$stmt_benevoles->execute();
$benevoles = $stmt_benevoles->fetchAll();

$stmtWasteItems = $pdo->prepare("SELECT type_dechet, quantite_kg FROM dechets_collectes WHERE id_collecte = ?");
$stmtWasteItems->execute([$id]);
$wasteItems = $stmtWasteItems->fetchAll();

$stmtWasteTypes = $pdo->query("SELECT DISTINCT type_dechet FROM dechets_collectes");
$wasteTypes = $stmtWasteTypes->fetchAll(PDO::FETCH_COLUMN);

if (empty($wasteItems)) {
    $wasteItems[] = ['type_dechet' => '', 'quantite_kg' => ''];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"];
    $lieu = $_POST["lieu"];
    $benevoles_ids = isset($_POST['benevoles']) ? $_POST['benevoles'] : [];
    $type_de_dechet = $_POST["type_dechet"];
    $quantite_dechet = $_POST["quantite_kg"];

    $stmtUpdate = $pdo->prepare("UPDATE collectes SET date_collecte = COALESCE(?, date_collecte), lieu = COALESCE(?, lieu) WHERE id = ?");
    if (!$stmtUpdate->execute([$date, $lieu, $id])) {
        die('Erreur lors de la mise à jour de la collecte.');
    }

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

    header($dashboardRedirection);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = "Modifier une collecte";
require 'headElement.php';
?>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">
        <!-- Barre de navigation -->
        <?php require 'navbar.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold text-blue-900 mb-6">Modifier une collecte</h1>

            <!-- Formulaire -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Date :
                            <input type="date" name="date" value="<?= htmlspecialchars($collecte['date_collecte']) ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Lieu :
                            <input type="text" name="lieu" value="<?= htmlspecialchars($collecte['lieu']) ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
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
                                    <select name="type_dechet[]" required class="w-full p-2 border border-gray-300 rounded-lg">
                                        <?= $selectOptions ?>
                                    </select>
                                    <input type="number" step="any" name="quantite_kg[]" placeholder="Quantité (kg)" value="<?= htmlspecialchars($item['quantite_kg']) ?>" required class="w-full p-2 border border-gray-300 rounded-lg">
                                    <button type="button" class="remove-waste bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                                        Supprimer
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="add-waste" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg mt-2">
                            Ajouter un déchet
                        </button>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <a href="collection_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">Annuler</a>
                        <button type="submit" class="bg-cyan-200 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow">
                            ✏️ Modifier
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
                    <option value="">Sélectionner un type</option>
                    <?php foreach ($wasteTypes as $wasteType): ?>
                        <option value="<?= htmlspecialchars($wasteType) ?>"><?= htmlspecialchars($wasteType) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="any" name="quantite_kg[]" placeholder="Quantité (kg)" required class="w-full p-2 border border-gray-300 rounded-lg">
                <button type="button" class="remove-waste bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                    Supprimer
                </button>
            </div>
        `;

        document.getElementById('add-waste').addEventListener('click', function() {
            const container = document.getElementById('waste-container');
            container.insertAdjacentHTML('beforeend', wasteRowTemplate);
        });

        // Gérer la suppression d'une ligne de déchet
        document.getElementById('waste-container').addEventListener('click', function(e) {
            if (e.target && e.target.matches('button.remove-waste')) {
                e.target.parentNode.remove();
            }
        });
    </script>
</body>

</html>