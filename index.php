<?php
  include 'scripts/php/estimation.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Comics DB</title>
  <link href="css/styles.css" rel="stylesheet" type="text/css"/>
</head>
<body>
  <div class="wrapper">
    <h1>eBay estimation</h1>

    <table>
      <thead>
        <tr>
          <th>Publisher</th>          
          <th>Title</th>
          <th>Volume</th>
          <th>Date</th> 
          <th>Issue</th>
          <th>Issue detail</th>
          <th>Variant</th>
          <th>Annual</th>                   
          <th>Era</th>
          <th>eBay query</th>
          <th>Estimated value</th>
        </tr>
      </thead>
      <tbody>
        <?php 
          $totalNAN = 0;
          $totalValue = array();
          foreach ($titles as $row) { ?>
        <tr>
          <td><?php echo getName($row['publisher'], 'publishers'); ?></td>          
          <td><?php echo $row['title']; ?></td>
          <td><?php echo $row['volume']; ?></td> 
          <td><?php echo $row['issue_date']; ?></td>
          <td><?php echo $row['issue']; ?></td>
          <td><?php echo $row['issue_detail']; ?></td>
          <td><?php 
              $variant = ($row['variant'] == 1 ? "Y" : "N");
              echo $variant;
           ?>
          </td>
          <td>
            <?php 
              $annual = ($row['annual'] == 1 ? "Y" : "N");
              echo $annual;
            ?>
          </td>                  
          <td><?php $era = getName($row['era'], 'eras'); echo $era; ?></td>
          <td>
            <?php 
              $query = getQuery($row['title'], $row['volume'], $row['issue'], $row['issue_detail'], $row['issue_date'], $row['variant'], $row['annual'], $row['notes']);
              echo $query;
            ?>
          </td>
          <td>
              <?php 
                  $value = getValue($query);
                  echo $value;                  
                  if(is_nan($value)) {
                    $totalNAN += 1;
                  } else {
                    array_push($totalValue, $value);
                  }
              ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
	</table>

	<p><strong>Total NANs:</strong> <?php echo $totalNAN; ?>/<?php echo (count($totalValue) + $totalNAN); ?></p>
	<p><strong>Total estimated value:</strong> &pound;<?php $totalEst = $totalNAN * 3; echo (array_sum($totalValue) + $totalEst); ?></p>

  </div>
  <script src="scripts/jquery-3.2.1.min.js"></script>
</body>
</html>