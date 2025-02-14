<?php
require 'config.php';

$volunteerListRedirection = "Location: volunteer_list.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header($volunteerListRedirection);
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT id, nom, email, role FROM benevoles WHERE id = ?");
$stmt->execute([$id]);
$benevole = $stmt->fetch();

if (!$benevole) {
    header($volunteerListRedirection);
    exit;
}

$stmtCollections = $pdo->query("SELECT id, CONCAT(date_collecte, ' - ', lieu) AS collection_label FROM collectes ORDER BY date_collecte");
$collections = $stmtCollections->fetchAll(PDO::FETCH_ASSOC);

$stmtAttendances = $pdo->prepare("SELECT id_collecte FROM benevoles_collectes WHERE id_benevole = ?");
$stmtAttendances->execute([$id]);
$currentAttendances = $stmtAttendances->fetchAll(PDO::FETCH_COLUMN, 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom"];
    $email = $_POST["email"];
    $password = $_POST["mot_de_passe"];
    $role = $_POST["role"];


    $stmtUpdate = $pdo->prepare("UPDATE benevoles SET nom = COALESCE(?, nom), email = COALESCE(?, email), mot_de_passe = COALESCE(?, mot_de_passe), role = COALESCE(?, role) WHERE id = ?");
    $stmtUpdate->execute([$nom, $email, $password, $role, $id]);

    $stmtDelete = $pdo->prepare("DELETE FROM benevoles_collectes WHERE id_benevole = ?");
    $stmtDelete->execute([$id]);

    if (isset($_POST['attendances']) && is_array($_POST['attendances'])) {
        $stmtInsert = $pdo->prepare("INSERT INTO benevoles_collectes (id_benevole, id_collecte) VALUES (?, ?)");
        foreach ($_POST['attendances'] as $collection_id) {
            $stmtInsert->execute([$id, $collection_id]);
        }
    }

    header($volunteerListRedirection);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = "Modifier un bénévole";
require 'headElement.php';
?>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">

        <!-- Barre de navigation -->
        <?php require 'navbar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold text-blue-900 mb-6">Modifier un benevole</h1>

            <!-- Formulaire -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            nom :
                            <input type="text" name="nom" value="<?= htmlspecialchars($benevole['nom']) ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            email :
                            <input type="email" name="email" value="<?= htmlspecialchars($benevole['email']) ?>" class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            mot de passe :
                            <input type="password" name="mot_de_passe" value="" class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Rôle:
                            <select name="role" class="w-full p-2 border border-gray-300 rounded-lg" required>
                                <option value="" disabled>Sélectionnez un rôle</option>
                                <option value="participant" <?= ($benevole['role'] == 'participant') ? 'selected' : '' ?>>Participant</option>
                                <option value="admin" <?= ($benevole['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </label>
                    </div>
                    <div class="mb-4">
                        <span class="block text-gray-700 font-medium mb-2">Participations (Collections):</span>
                        <?php if (!empty($collections)): ?>
                            <?php foreach ($collections as $collection): ?>
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" name="attendances[]" value="<?= htmlspecialchars($collection['id']) ?>" id="collection_<?= htmlspecialchars($collection['id']) ?>" class="mr-2"
                                        <?= in_array($collection['id'], $currentAttendances) ? 'checked' : '' ?>>
                                    <label for="collection_<?= htmlspecialchars($collection['id']) ?>" class="text-gray-700">
                                        <?= htmlspecialchars($collection['collection_label']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500">Aucune collecte n'est disponible pour l'instant.</p>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="volunteer_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">Annuler</a>
                        <button type="submit" class="bg-cyan-200 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow-md font-semibold">
                            Modifier le bénévole
                        </button>
                    </div>
                </form>
            </div>
    </div>
    </div>

</body>

</html>