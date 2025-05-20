<?php
ob_start("ob_gzhandler");
require 'FeedHandler.php';

$items = get_news();
$channels = get_channels();

// Add a new channel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_channel'])) {
	$url = trim($_POST['channel_url']);
    if (!empty($url)) add_channel($url);
	$channels = get_channels();
}

// Update news
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_news'])) {
	update_news();
	$items = get_news();
}

// Search
$searchQuery = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
	$searchQuery = trim($_GET['search']);
    if (!empty($searchQuery)) {
		$items = search_news($searchQuery);
    }
}

// Sorting
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sort'])) {
	$sortBy = $_GET['sort'];
	$items = get_news($sortBy);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feed RSS</title>
    <link rel="stylesheet" href="Styles.php">
</head>
<body>
	
	<!-- Add a new channel -->
	<form method="POST" action="">
		<label for="channel_url">Añade un nuevo sitio a tu feed:</label>
		<input type="text" id="channel_url" name="channel_url" placeholder="Inserta el URL del RSS" required>
		<button type="submit" name="add_channel">Añadir sitio</button>
	</form>


	<!-- Channels -->
    <h2>Tus sitios:</h2>
    <div class="channels-container">
        <?php if (!empty($channels)) : ?>
            <?php foreach ($channels as $channel) : ?>
                <div class="channel">
                    <b><?= htmlspecialchars($channel['title']) ?></b>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No tienes sitios registrados</p>
        <?php endif; ?>
    </div>
	

	<div class="header">
		<h1>Feed de noticias</h1>
        <div class="search">
            <!-- Sorting -->
            <form method="GET" action="">
                <label for="sort">Ordenar por:</label>
                <select id="sort" name="sort" onchange="this.form.submit()">
                    <option value=""></option>
                    <option value="pub_date">Fecha</option>
                    <option value="title">Título</option>
                    <option value="description">Descripción</option>
                </select>
            </form>

            <!-- Search bar -->
            <form method="GET" action="">
                <label for="search">Buscar noticias:</label>
                <input type="text" id="search" name="search" placeholder="Buscar por título o descripción" value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit">Buscar</button>
            </form>

            <!-- Update news -->
            <form  method="POST" action="">
                <button id = "button-update" type="submit" name="update_news">Actualizar noticias</button>
            </form>

        </div>
	</div>



    <!-- Feed -->
    <div class="items-container">
    <?php if (!empty($items)) : ?>
        <?php foreach ($items as $item) : ?>
            <div class="item">
                <div class="title">
                    <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank">
                        <?= htmlspecialchars($item['title']) ?>
                    </a>
                </div>
                <div class="date">
                   <b><?= htmlspecialchars(date('d / M / Y', strtotime($item['pub_date']))) ?></b> 
                </div>
                <div class="description">
                    <?php
                        $description = strip_tags(html_entity_decode($item['description']));
                        if (strlen($description) > 300) {
                            $description = substr($description, 0, 300) . '...';
                        }
                        echo htmlspecialchars($description);
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>

    <footer>
        <p>Feed RSS</p>
    </footer>
   
</body>
</html>