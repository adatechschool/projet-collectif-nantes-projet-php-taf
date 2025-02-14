<?php
require 'config.php';

$volunteerListRedirection = "Location: volunteer_list.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header($volunteerListRedirection);
    exit;
}
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM benevoles WHERE id = ?");
$stmt->execute([$id]);
$benevole = $stmt->fetch();

if (!$benevole) {
    header($volunteerListRedirection);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom"];
    $email = $_POST["email"];
    $password = $_POST["mot_de_passe"];
    $role = $_POST["role"];


    $stmt = $pdo->prepare("UPDATE benevoles SET nom = ?, email = ?, mot_de_passe = ?, role = ? WHERE id = ?");
    $stmt->execute([$nom, $email, $password, $role, $id]);

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
                            <input type="text" name="nom" value="<?= htmlspecialchars($benevole['nom']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            email :
                            <input type="email" name="email" value="<?= htmlspecialchars($benevole['email']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            mot de passe :
                            <input type="password" name="mot_de_passe" value="<?= htmlspecialchars($benevole['mot_de_passe']) ?>" required class="w-full p-2 border border-gray-300 rounded-lg">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            role :
                            <select name="role" required
                                class="w-full p-2 border border-gray-300 rounded-lg">
                                <option value="" disabled selected>Sélectionnez un role</option>
                                <option value="participant">Participant</option>
                                <option value="admin">Admin</option>
                            </select>
                        </label>
                    </div>
                    <?php
                    $cancellationUrl = "volunteer_list.php";
                    require 'edit_buttons.php';
                    ?>
                </form>
            </div>
    </div>
    </div>

</body>

</html>