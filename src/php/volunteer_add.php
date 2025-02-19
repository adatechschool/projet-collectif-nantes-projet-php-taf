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

require "config.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ------------------------------------- */
// On récupère toutes les collectes organisées
$sqlQueryCollections = "SELECT id, CONCAT(date_collecte, ' - ', lieu) AS collection_label FROM collectes ORDER BY date_collecte";
$statementCollections = $pdo->query($sqlQueryCollections);
$collections = $statementCollections->fetchAll();
/* ========================================== */

/* ------------------------------------- */
// Lorsque l'utilisateur·trice soumet le formulaire...
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['nom'];
    $email = $_POST['email'];
    $password = $_POST['mot_de_passe'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $participations = $_POST['attendances'];

    try {
        /* ------------------------------------- */
        // 1. On insère le bénévole dans la base de données
        $sqlQuery = "INSERT INTO benevoles(nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)";
        $statement = $pdo->prepare($sqlQuery);
        if (!$statement->execute([$name, $email, $hashedPassword, $role])) {
            die("Erreur lors de l'insertion du bénévole dans la base de données.");
        }
        /* ========================================== */

        /* ------------------------------------- */
        // 2. On précise les collectes auxquelles le bénévole a participé
        $id_benevole = $pdo->lastInsertId();
        if (isset($participations)) {
            $sqlQueryJoinTable = "INSERT INTO benevoles_collectes (id_benevole, id_collecte) VALUES (?, ?)";
            $statementJoinTable = $pdo->prepare($sqlQueryJoinTable);
            foreach ($participations as $id_collecte) {
                if (!$statementJoinTable->execute([$id_benevole, $id_collecte])) {
                    die("Erreur lors de l'assignation des collectes.");
                }
            }
        }
        /* ========================================== */

        header("Location: volunteer_list.php");
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/projet-collectif-nantes-projet-php-taf/src/css/style.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />
    <title>Ajouter un bénévole</title>
</head>

<body class="bg-gray-100 text-gray-900">

    <div class="flex h-screen">

        <!-- ---- Barre de navigation ---- -->
        <?php require 'navbar.php'; ?>
        <!-- ======================= -->

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold text-cyan-950 mb-6">Ajouter un Bénévole</h1>

            <!-- ------------------ Formulaire ------------------ -->
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg mx-auto">
                <form method="POST" action="volunteer_add.php" class="space-y-4">
                    <!-- ------------------ champ Nom ------------------ -->
                    <div class="mb-4">
                        <label for="nom" class="block text-sm font-medium text-gray-700">
                            Nom
                            <input type="text" name="nom"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Nom du bénévole" required />
                        </label>
                    </div>
                    <!-- ============================================== -->

                    <!-- ------------------ champ Email ------------------ -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                            <input type="email" name="email"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Email du bénévole" required />
                        </label>
                    </div>
                    <!-- ============================================== -->

                    <!-- ------------------ champ Mot de passe ------------------ -->
                    <div class="mb-4">
                        <label for="mot_de_passe" class="block text-sm font-medium text-gray-700">
                            Mot de passe
                            <input type="password" name="mot_de_passe"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Mot de passe" required />
                        </label>
                    </div>
                    <!-- ============================================== -->

                    <!-- ------------------ champ Rôle ------------------ -->
                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700">
                            Rôle
                            <select name="role"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="participant">Participant</option>
                                <option value="admin">Admin</option>
                            </select>
                        </label>
                    </div>
                    <!-- ============================================== -->

                    <!-- ------------------ champ Participations ------------------ -->
                    <div class="mb-4">
                        <label for="attendances[]" class="block text-sm font-medium text-gray-700 mb-2">
                            Participations
                            <?php if (!empty($collections)): ?>
                                <?php foreach ($collections as $collection): ?>
                                    <div class="flex items-center mb-2">
                                        <input type="checkbox" name="attendances[]" value="<?= htmlspecialchars($collection['id']) ?>" id="collection_<?= htmlspecialchars($collection['id']) ?>" class="mr-2">
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
                            Ajouter le bénévole
                        </button>
                    </div>
                    <!-- ============================================== -->
                </form>
            </div>
    </div>
    </div>

</body>

</html>