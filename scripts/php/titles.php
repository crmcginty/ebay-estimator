<?php

  include "db_objects.php";

  // Query to get publishers
  function getPublishers() {
  	$db = new DB();
    $publishers = $db -> query("SELECT * FROM publisher");
    echo json_encode($publishers); // Return the results as JSON
    exit;   
  }

  // Query to get comic titles
  function getTitles() {
  	$db = new DB();
    $titles = $db -> query("SELECT p.name AS publisher, e.name AS era, n.last_name AS cover, t.id, t.issue, t.issue_date, t.issue_detail, t.notes, t.quantity, t.annual, t.special, t.title, t.variant, t.cgc_grading
      FROM titles AS t
      INNER JOIN publisher AS p ON t.publisher = p.id
      LEFT OUTER JOIN eras AS e
        ON CASE
        WHEN t.era IS NOT NULL THEN t.era = e.id
        ELSE t.era
        END
      LEFT OUTER JOIN names AS n ON t.cover = n.id
      ORDER BY title ASC");

    echo json_encode($titles); // Return the results as JSON
    exit;
  }

  if (isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
      case 'getTitles':
        getTitles();
        break;
      case 'getPublishers':
        getPublishers();
        break;
      default: break;
    }
  }
?>