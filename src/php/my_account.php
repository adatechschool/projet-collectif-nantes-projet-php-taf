<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit();
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
            <h1 class="text-4xl font-bold mb-6">Paramètres</h1>

            <!-- Message de succès ou d'erreur -->
            <div class="text-green-600 text-center mb-4" id="success-message" style="display:none;">
                Vos paramètres ont été mis à jour avec succès.
            </div>
            <div class="text-red-600 text-center mb-4" id="error-message" style="display:none;">
                Le mot de passe actuel est incorrect.
            </div>

            <form id="settings-form" class="space-y-6">
                <!-- Champ Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="exemple@domaine.com" required
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Champ Mot de passe actuel -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Mot de passe
                        actuel</label>
                    <input type="password" name="current_password" id="current_password" required
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
                    <button type="button" onclick="updateSettings()"
                        class="bg-cyan-200 hover:bg-cyan-600 text-white px-6 py-2 rounded-lg shadow-md">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>

</html>