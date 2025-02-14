<?php
require 'config.php';

$dashboardRedirection = "Location: collection_list.php";

// Vérifier si un ID de collecte est fourni
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

// Récupérer la liste des bénévoles
$stmt_benevoles = $pdo->prepare("SELECT id, nom FROM benevoles ORDER BY nom");
$stmt_benevoles->execute();
$benevoles = $stmt_benevoles->fetchAll();

// Mettre à jour la collecte
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"];
    $lieu = $_POST["lieu"];
    $benevole_id = $_POST["benevole"]; // Récupérer l'ID du bénévole sélectionné
    $type_de_dechet = $_POST["type_dechet"];
    $quantite_dechet = $_POST["quantite_kg"];
    $collecte_id = $_POST["collecte"];

    $stmt = $pdo->prepare("UPDATE collectes SET date_collecte = ?, lieu = ?, id_benevole = ? WHERE id = ?");
    $stmt->execute([$date, $lieu, $benevole_id, $id]);

    $stmt2 = $pdo->prepare("INSERT INTO dechets_collectes (id_collecte, type_dechet, quantite_kg) VALUES (?,?,?)");
    $stmt2->execute([$id, $type_de_dechet, $quantite_dechet]);
    header("Location: collection_list.php");
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
                            <input type="date" name="date" value="<?= htmlspecialchars($collecte['date_collecte']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Lieu :
                            <input type="text" name="lieu" value="<?= htmlspecialchars($collecte['lieu']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Bénévole :
                            <select name="benevole" required
                                class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="" disabled selected>Sélectionnez un·e bénévole</option>
                                <?php foreach ($benevoles as $benevole): ?>
                                    <option value="<?= $benevole['id'] ?>" <?= $benevole['id'] == $collecte['id_benevole'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($benevole['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Type de déchet :
                            <select name="type_dechet" required class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="" disabled selected>Sélectionnez le type</option>
                                <option value="plastique">Plastique</option>
                                <option value="verre">Verre</option>
                                <option value="papier">Papier</option>
                                <option value="metal">Métal</option>
                                <option value="organique">Organique</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Quantité (kg) :
                            <input type="number" name="quantite_kg" step="0.1" min="0" required
                                class="w-full p-2 border border-gray-300 rounded-lg"
                                value="<?= htmlspecialchars($collecte['quantite_kg'] ?? '') ?>">
                        </label>
                    </div>
                    <?php
                    $cancellationUrl = "collection_list.php";
                    require 'edit_buttons.php';
                    ?>
                </form>
            </div>
    </div>
    </div>
</body>

</html>