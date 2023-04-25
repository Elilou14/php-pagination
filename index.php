<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Liste des films</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			background-color: #F9F9F9;
		}
		table {
			border-collapse: collapse;
			margin: 20px auto;
			background-color: #FFFFFF;
			box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
			width: 80%;
		}
		thead {
			background-color: #F2F2F2;
			font-weight: bold;
		}
		th, td {
			padding: 10px;
			text-align: left;
			border-bottom: 1px solid #CCCCCC;
		}
		th a {
			color: #333333;
			text-decoration: none;
			display: block;
			position: relative;
		}
		th a:hover {
			color: #222222;
		}
		th a:before {
			content: "";
			display: block;
			height: 6px;
			width: 6px;
			border-bottom: 1px solid #333333;
			border-right: 1px solid #333333;
			transform: rotate(-45deg);
			position: absolute;
			right: 15px;
			top: 50%;
			margin-top: -4px;
		}
		th a.asc:before {
			transform: rotate(135deg);
			border-bottom: 1px solid #FFFFFF;
			border-right: 1px solid #FFFFFF;
			border-top: 1px solid #333333;
			border-left: 1px solid #333333;
		}
		th a.desc:before {
			transform: rotate(-135deg);
			border-top: 1px solid #FFFFFF;
			border-left: 1px solid #FFFFFF;
			border-bottom: 1px solid #333333;
			border-right: 1px solid #333333;
		}
		tbody tr:nth-child(odd) {
			background-color: #F9F9F9;
		}
		tbody tr:hover {
			background-color: #F2F2F2;
		}
		.pagination {
			margin: 20px auto;
			text-align: center;
			font-size: 14px;
		}
		.pagination a {
			display: inline-block;
			padding: 6px 12px;
			background-color: #FFFFFF;
			border: 1px solid #DDDDDD;
			color: #333333;
			text-decoration: none;
			margin-right: 5px;
		}
		.pagination a:hover {
			background-color: #F2F2F2;
			border: 1px solid #BBBBBB;
		}
		.pagination .current {
			background-color: #0073AA;
			border: 1px solid #0073AA;
			color: #FFFFFF;
		}
	</style>
</head>
<body>

<?php
// Connexion à la base de données
$servername = "unixshell.hetic.glassworks.tech";
$username = "student";
$password = "Tk0Uc2o2mwqcnIA";
$dbname = "sakila";
$port = "27116";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Paramètres de pagination
$results_per_page = 10;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// Paramètres de tri
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'film_title';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Requête SQL pour récupérer les données des films
$sql = "SELECT film.title as film_title, film.rental_rate, film.rating, category.name as category_name, COUNT(rental.rental_id) as rental_count
        FROM film
        JOIN film_category ON film.film_id = film_category.film_id
        JOIN category ON film_category.category_id = category.category_id
        JOIN inventory ON film.film_id = inventory.film_id
        JOIN rental ON inventory.inventory_id = rental.inventory_id
        GROUP BY film.film_id
        ORDER BY $sort_by $sort_order
        LIMIT $offset, $results_per_page";

// Exécution de la requête
$result = $conn->query($sql);

// Requête SQL pour récupérer le nombre total de résultats
$sql_count = "SELECT COUNT(*) as count FROM film";
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_results = $row_count['count'];

// Calcul du nombre total de pages
$total_pages = ceil($total_results / $results_per_page);

// Affichage des résultats
echo "<table class='table table-dark table-striped'>
        <thead>
            <tr>
                <th><a href='?sort_by=film_title&sort_order=".($sort_by=='film_title' && $sort_order=='ASC' ? 'DESC' : 'ASC')."'>Nom du film</a></th>
                <th>Prix de location</th>
                <th><a href='?sort_by=rating&sort_order=".($sort_by=='rating' && $sort_order=='ASC' ? 'DESC' : 'ASC')."'>Classement</a></th>
                <th><a href='?sort_by=category_name&sort_order=".($sort_by=='category_name' && $sort_order=='ASC' ? 'DESC' : 'ASC')."'>Genre du film</a></th>
                <th><a href='?sort_by=rental_count&sort_order=".($sort_by=='rental_count' && $sort_order=='ASC' ? 'DESC' : 'ASC')."'>Nombre de locations</a></th>
            </tr>
        </thead>
        <tbody>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row['film_title']."</td>
                <td>".$row['rental_rate']."</td>
                <td>".$row['rating']."</td>
                <td>".$row['category_name']."</td>
                <td>".$row['rental_count']."</td>
                </tr>";
                }
                } else {
                echo "<tr><td colspan='5'>Aucun résultat trouvé.</td></tr>";
                }
                echo "</tbody>
                </table>";

                // Affichage de la pagination
                echo "<div class='pagination' >";
                if ($total_results > $results_per_page) {
                if ($current_page > 1) {
                echo "<a href='?page=".($current_page - 1)."&sort_by=$sort_by&sort_order=$sort_order'>Précédent</a>";
                }
                for ($i = 1; $i <= $total_pages; $i++) {
                echo "<a href='?page=$i&sort_by=$sort_by&sort_order=$sort_order'".($i==$current_page ? " class='current'" : "").">$i</a>";
                }
                if ($current_page < $total_pages) {
                echo "<a href='?page=".($current_page + 1)."&sort_by=$sort_by&sort_order=$sort_order'>Suivant</a>";
                }
                }
                echo "</div>";

                // Fermeture de la connexion
                $conn->close();
                ?>
