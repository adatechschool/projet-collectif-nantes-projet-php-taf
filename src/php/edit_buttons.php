<?php
// action_buttons.php

// Ensure the necessary variables are set. If not, set default values.
if (!isset($cancellationUrl)) {
    $cancellationUrl = "#";
}
if (!isset($confirmMessage)) {
    $confirmMessage = "Êtes-vous sûr de vouloir annuler la modification ?";
}
?>

<section id="edition" class="flex justify-end space-x-4">
    <a href="<?= htmlspecialchars($cancellationUrl) ?>" class="bg-gray-500 text-white px-4 py-2 rounded-lg">Annuler</a>
    <button type="submit" class="bg-cyan-200 text-white px-4 py-2 rounded-lg">Modifier</button>
</section>