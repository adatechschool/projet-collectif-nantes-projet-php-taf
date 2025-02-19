<?php
session_start();
if(!isset($_SESSION["user_id"])){
    header('Location: login.php');
    exit();
}

require 'config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $statement = $pdo->prepare("DELETE FROM benevoles WHERE id = :id");

        $statement->bindParam(':id', $id, PDO::PARAM_INT);

        if ($statement->execute()) {
            header("Location: volunteer_list.php?success=1");
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
