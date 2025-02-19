<?php
session_start();

/* ------------------------------------- */
// On vérifie que l'utilisateur·trice est connecté·e et qu'il·elle est un·e admin
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: volunteer_list.php");
    exit();
}
/* ========================================== */

require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);


/* ------------------------------------- */
// On redirige vers la liste des bénévoles si l'utilisateur·trice à modifier n'existe pas ou n'a pas été récupéré·e correctement.
$volunteerListRedirection = "Location: volunteer_list.php";
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header($volunteerListRedirection);
    exit;
}
/* ========================================== */

/* ------------------------------------- */
// On récupère les données du bénévole à modifier
$sqlQuery = "SELECT id, role FROM benevoles WHERE id = ?";
$statement = $pdo->prepare($sqlQuery);
$id_benevole = $_GET['id'];
$statement->execute([$id_benevole]);
$benevole = $statement->fetch();
if (!$benevole) {
    header($volunteerListRedirection);
    exit;
}
/* ========================================== */

/* ------------------------------------- */
// On récupère toutes les collectes organisées
$sqlQueryCollections = "SELECT id, CONCAT(date_collecte, ' - ', lieu) AS collection_label FROM collectes ORDER BY date_collecte";
$statementCollections = $pdo->query($sqlQueryCollections);
$collections = $statementCollections->fetchAll();
/* ========================================== */

/* ------------------------------------- */
// On récupère les collectes auxquelles le·la bénévole a participé·e
$sqlQueryAttendances = "SELECT id_collecte FROM benevoles_collectes WHERE id_benevole = ?";
$statementAttendances = $pdo->prepare($sqlQueryAttendances);
$statementAttendances->execute([$id_benevole]);
$currentAttendances =
    array_column($statementAttendances->fetchAll(), 'id_collecte');
/* ========================================== */

/* ------------------------------------- */
// Lorsque l'utilisateur·trice soumet le formulaire...
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST["role"];
    $participations = $_POST['attendances'];

    try {
        /* ------------------------------------- */
        // 1. On met à jour le rôle du bénévole.
        // La fonction COALESCE permet de ne pas mettre à jour les champs vides.
        $sqlQueryUpdate = "UPDATE benevoles SET role = COALESCE(?, role) WHERE id = ?";
        $statementUpdate = $pdo->prepare($sqlQueryUpdate);
        if (!$statementUpdate->execute([$role, $id_benevole])) {
            die("Erreur lors de la mise à jour du rôle du bénévole.");
        }
        /* ========================================== */

        /* ------------------------------------- */
        // 2. On modifie les collectes auxquelles le·la bénévole a participé·e
        // Il s'agit d'une opération qui se fait en deux étapes :
        // - D'abord, on supprime toutes les collectes auxquelles le bénévole a participé.
        // - Ensuite, on insère les nouvelles collectes auxquelles le bénévole a participé.
        $sqlQueryDelete = "DELETE FROM benevoles_collectes WHERE id_benevole = ?";
        $statementDelete = $pdo->prepare($sqlQueryDelete);
        $statementDelete->execute([$id_benevole]);

        if (isset($participations) && is_array($participations)) {
            $sqlQueryInsert = "INSERT INTO benevoles_collectes (id_benevole, id_collecte) VALUES (?, ?)";
            $statementInsert = $pdo->prepare($sqlQueryInsert);
            foreach ($participations as $id_collecte) {
                if (!$statementInsert->execute([$id_benevole, $id_collecte])) {
                    die("Erreur lors de l'assignation des collectes.");
                }
            }
        }
        /* ========================================== */

        header($volunteerListRedirection);
        exit;
    } catch (PDOException $e) {
        echo "Erreur de base de données : " . $e->getMessage();
        exit;
    }
}
/* ========================================== */
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require 'headElement.php'; ?>
    <title>Modifier un bénévole</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">

        <!-- ---- Barre de navigation ---- -->
        <?php require 'navbar.php'; ?>
        <!-- ======================= -->

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold text-cyan-950 mb-6">Modifier un benevole</h1>

            <!-- ------------------ Formulaire ------------------ -->
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg mx-auto">
                <form method="POST" class="space-y-4">
                    <!-- ------------------ champ Rôle ------------------ -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Rôle
                            <select name="role" class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="" disabled>Sélectionnez un rôle</option>

                                <option value="participant" <?= ($benevole['role'] === 'participant') ? 'selected' : '' ?>>
                                    Participant
                                </option>

                                <option value="admin" <?= ($benevole['role'] === 'admin') ? 'selected' : '' ?>>
                                    Admin
                                </option>
                            </select>
                        </label>
                    </div>
                    <!-- ============================================== -->

                    <!-- -------------- champ Participations -------------- -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Participations
                            <?php if (!empty($collections)): ?>
                                <?php foreach ($collections as $collection): ?>
                                    <div class="flex items-center mb-2">
                                        <input type="checkbox" name="attendances[]" value="<?= htmlspecialchars($collection['id']) ?>" id="collection_<?= htmlspecialchars($collection['id']) ?>" class="mr-2" <?= in_array($collection['id'], $currentAttendances) ? 'checked' : '' ?> />
                                        <label for="collection_<?= htmlspecialchars($collection['id']) ?>" class="text-gray-700">
                                            <?= htmlspecialchars($collection['collection_label']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500">Aucune collecte n'est disponible pour l'instant.</p>
                            <?php endif; ?>
                        </label>
                    </div>
                    <!-- ============================================== -->

                    <!-- ------------------ boutons ------------------ -->
                    <div class="flex justify-end space-x-4">
                        <a href="volunteer_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">Annuler</a>
                        <button type="submit" class="bg-cyan-950 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md font-semibold">
                            Modifier le bénévole
                        </button>
                    </div>
                    <!-- ============================================== -->
                </form>
            </div>
            <!-- ============================================ -->
    </div>
    </div>

</body>

</html>