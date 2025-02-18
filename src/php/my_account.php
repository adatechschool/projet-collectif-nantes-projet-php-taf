<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit();
}
require "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom"];
    $email = $_POST["email"];
    $currentPassword = $_POST["current_password"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    // Vérification et mise à jour du mot de passe
    if (!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)) {
        // Récupérer le mot de passe actuel depuis la base de données
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM benevoles WHERE id = ?");
        $stmt->execute([$_SESSION["user_id"]]);
        $user = $stmt->fetch();

        // Vérifier si le mot de passe actuel est correct
        if ($user && password_verify($currentPassword, $user['mot_de_passe'])) {
            if ($newPassword === $confirmPassword) {
                // Hacher le nouveau mot de passe et le mettre à jour
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmtUpdatePassword = $pdo->prepare("UPDATE benevoles SET mot_de_passe = ? WHERE id = ?");
                $stmtUpdatePassword->execute([$hashedPassword, $_SESSION["user_id"]]);
            } else {
                $error = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
            }
        } else {
            $error = "Le mot de passe actuel est incorrect.";
        }
    }

    $stmtUpdate = $pdo->prepare("UPDATE benevoles SET nom = COALESCE(?, nom), email = COALESCE(?, email) 
     WHERE id = ?");
    $stmtUpdate->execute([$nom, $email, $_SESSION["user_id"]]);
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = "Paramètres";
require 'headElement.php';
?>

<body class="bg-gray-100 text-gray-900">
    <div class="flex h-screen">
        <!-- Barre de navigation -->
        <?php require 'navbar.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- Titre -->
            <h1 class="text-4xl text-cyan-950 font-bold mb-6">Mon compte</h1>

            <!-- Message de succès ou d'erreur -->
            <?php if (!empty($error)) : ?>
                <div class="text-red-600 text-center mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form id="settings-form" method="POST" class="space-y-6">
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700">nom</label>
                    <input type="nom" name="nom" id="nom" value="<?= htmlspecialchars($_SESSION['nom']) ?>"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <!-- Champ Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($_SESSION['email']) ?>"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Champ Mot de passe actuel -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Mot de passe
                        actuel</label>
                    <input type="password" name="current_password" id="current_password"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Champ Nouveau Mot de passe -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                    <input type="password" name="new_password" id="new_password"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Champ Confirmer le nouveau Mot de passe -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmer le mot de
                        passe</label>
                    <input type="password" name="confirm_password" id="confirm_password"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Boutons -->
                <div class="flex justify-between items-center">
                    <a href="collection_list.php" class="text-sm text-blue-600 hover:underline">Retour à la liste des
                        collectes</a>
                    <button type="submit"
                        class="bg-cyan-200 hover:bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>

</html>