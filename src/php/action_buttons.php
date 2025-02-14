<?php
// action_buttons.php

// Ensure the necessary variables are set. If not, set default values.
if (!isset($editUrl)) {
    $editUrl = "#";
}
if (!isset($deleteUrl)) {
    $deleteUrl = "#";
}
if (!isset($confirmMessage)) {
    $confirmMessage = "Êtes-vous sûr de vouloir supprimer cet élément ?";
}
?>

<section id="actions" class="flex space-x-2">
    <a href="<?= htmlspecialchars($editUrl) ?>"
        class="text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
        ✏️ Modifier
    </a>
    <a href=" <?= htmlspecialchars($deleteUrl) ?>"
        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
        onclick="return confirm('<?= addslashes($confirmMessage) ?>');">
        🗑️ Supprimer
    </a>
</section>