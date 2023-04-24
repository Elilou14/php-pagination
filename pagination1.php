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
echo "<table>
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
                echo "<div>";
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
