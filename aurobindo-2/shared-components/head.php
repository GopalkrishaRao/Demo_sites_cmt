<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="keywords" content="">
<?php
$pagename = $_SERVER['REQUEST_URI'];
$pagename = basename($pagename);         // $file is set to "index.php"
$pagename = basename($pagename, ".php"); // $file is set to "index"

$actualpagename = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$actualpagename = basename($actualpagename);         // $file is set to "index.php"
$actualpagename = basename($actualpagename, ".php"); // $file is set to "index"

if ($actualpagename === 'blog-details') {
    $StoreURL = 'http://localhost/aurabindo';
    // $StoreURL = 'https://staywell.codemythought.com/';
} else {
    $StoreURL = '';
}
?>

<link rel="canonical" href="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" />


<title>Aurobindo </title>
<link rel="icon" type="image/x-icon" href="assets/img/logo/favicon.png">

<!-- css -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/all-fontawesome.min.css">
<link rel="stylesheet" href="assets/css/animate.min.css">
<link rel="stylesheet" href="assets/css/magnific-popup.min.css">
<link rel="stylesheet" href="assets/css/owl.carousel.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/custom.css">