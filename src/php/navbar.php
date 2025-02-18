<?php 
// session_start();
// ajouter une collecte
// ajouter un bénévole
?>

<nav role="navigation" class="bg-cyan-950 text-white w-64 p-6">
  <h2 class="text-2xl font-bold mb-6">Dashboard</h2>
  <ul>
    <li>
      <a
        href="collection_list.php"
        class="flex items-center py-2 px-3 hover:bg-blue-600 rounded-lg"><i class="fas fa-tachometer-alt mr-3"></i> Tableau de bord</a>
    </li>
    <?php if($_SESSION["role"] !== "admin"): ?>
      <?php else: ?>
    <li>
      <a
        href="collection_add.php"
        class="flex items-center py-2 px-3 hover:bg-blue-600 rounded-lg"><i class="fas fa-plus-circle mr-3"></i> Ajouter une collecte</a>
    </li>
    <?php endif ?>
    <li>
      <a
        href="volunteer_list.php"
        class="flex items-center py-2 px-3 hover:bg-blue-600 rounded-lg"><i class="fa-solid fa-list mr-3"></i> Liste des bénévoles</a>
    </li>
    <?php if($_SESSION["role"] !== "admin"): ?>
      <?php else: ?>
    <li>
      <a
        href="volunteer_add.php"
        class="flex items-center py-2 px-3 hover:bg-blue-600 rounded-lg">
        <i class="fas fa-user-plus mr-3"></i> Ajouter un bénévole
      </a>
    </li>
    <?php endif ?>
    <li>
      <a
        href="my_account.php"
        class="flex items-center py-2 px-3 hover:bg-blue-600 rounded-lg"><i class="fas fa-cogs mr-3"></i> Mon compte</a>
    </li>
  </ul>
  <div class="mt-6">
    <a
      href="logout.php"
      class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg shadow-md">
      Déconnexion
    </a>
  </div>
</nav>