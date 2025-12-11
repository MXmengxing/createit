<?php 
$current = basename($_SERVER['PHP_SELF']); 
?>
<header>
    <nav>
        <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="analyse.php" class="<?= $current === 'analyse.php' ? 'active' : '' ?>">Interactieve Analyse</a>
        <a href="portefeuille.php" class="<?= $current === 'portefeuille.php' ? 'active' : '' ?>">Portefeuille</a>
    </nav>

    <button id="themeToggle" class="toggle">Donker</button>
</header>
