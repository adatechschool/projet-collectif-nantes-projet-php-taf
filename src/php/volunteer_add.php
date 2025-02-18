<?php
session_start();
if(!isset($_SESSION["user_id"])){
    header('Location: login.php');
    exit();
}

require "config.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$stmtCollections = $pdo->query("SELECT id, CONCAT(date_collecte, ' - ', lieu) AS collection_label FROM collectes ORDER BY date_collecte");
$collections = $stmtCollections->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['nom'];
    $email = $_POST['email'];
    $password = $_POST['mot_de_passe'];
    $role = $_POST['role'];

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $statement = $pdo->prepare("INSERT INTO benevoles(nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        if (!$statement->execute([$name, $email, $hash, $role])) {
            die("Erreur lors de l'insertion dans la base de données.");
        }

        $volunteer_id = $pdo->lastInsertId();

        if (isset($_POST['attendances'])) {
            $stmtBC = $pdo->prepare("INSERT INTO benevoles_collectes (id_benevole, id_collecte) VALUES (?, ?)");
            foreach ($_POST['attendances'] as $collection_id) {
                $stmtBC->execute([$volunteer_id, $collection_id]);
            }
        }

        header("Location: volunteer_list.php");
        exit;
    } catch (PDOException $e) {
        echo "Erreur de base de données : " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = "Ajouter un Bénévole";
require 'headElement.php';
?>

<body class="bg-gray-100 text-gray-900">

    <div class="flex h-screen">
        <!-- Barre de navigation -->
        <?php require 'navbar.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold mb-6">Ajouter un Bénévole</h1>

            <!-- Formulaire d'ajout -->
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg mx-auto">
                <form action="volunteer_add.php" method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">
                            Nom
                            <input type="text" name="nom"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Nom du bénévole" required>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">
                            Email
                            <input type="email" name="email"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Email du bénévole" required>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">
                            Mot de passe
                            <input type="password" name="mot_de_passe"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Mot de passe" required>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium">
                            Rôle
                            <select name="role"
                                class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="participant">Participant</option>
                                <option value="admin">Admin</option>
                            </select>
                        </label>
                    </div>

                    <div class="mb-4">
                        <span class="block text-gray-700 font-medium mb-2">Participations :</span>
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
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="volunteer_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">Annuler</a>
                        <button type="submit" class="bg-cyan-200 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg shadow-md font-semibold">
                            Ajouter le bénévole
                        </button>
                    </div>
                </form>
            </div>
    </div>
    </div>

</body>

</html>