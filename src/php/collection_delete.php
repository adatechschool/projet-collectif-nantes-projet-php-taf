<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit();
}

require 'config.php';

/* -------------------------------- */
// On vérifie qu'on a bien récupéré l'ID de la collecte à supprimer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM collectes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = urlencode("La collecte a été supprimée avec succès");
            header("Location: collection_list.php?message={$message}");
            exit();
        } else {
            echo "Erreur lors de la suppression.";
        }
    } catch (PDOException $e) {
        die("Erreur: " . $e->getMessage());
    }
} else {
    echo "ID invalide.";
}
/* ======================================= */
