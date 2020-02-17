<?php
session_start();
if($_SESSION['login'] != TRUE) {
    header('Location: index.php');
}

$expire = "1 hour";

date_default_timezone_set("UTC");
require './vendor/autoload.php';
include 'config.php';
require_once 'Image.class.php';
?>
<html lang="en">
<head>
    <!needed this to stop a warning in the validator>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>VaporWav - Share your art</title>
    <link rel="stylesheet" href="stylesJ.css">
</head>
<body>
  <header>
    <div class="padThis">
      <h1>VaporWav</h1>
      <p class="underHeader">Show us what you have been working on.</p>
    </div>
    <nav>
    <!list of the seperate parts of this page>
    <ul>
      <li><a href = "home.php">Home</a>
      <a href = "uploadPage.php">Upload</a></li>
    </ul>
    <ul class="leftHead">
      <li><a href = "account.php">My Account</a>
      <a href = "logout.php">Logout</a></li>
    </ul>
    </nav>
  </header>



<main class="container2">
<h2>Your Gallery</h2>
<br>
    <section class="cards">
<?php

$email = $_SESSION['userData']['email'];

$s3 = new Aws\S3\S3Client([
    'version' => '2006-03-01',
    'region'  => $region,
]);

$bucket_url = "https://s3-{$region}.amazonaws.com/{$bucket}";

$iterator = $s3->getIterator('ListObjects', array('Bucket' => $bucket, 'Prefix' => $email));

//$images = array();

foreach ($iterator as $object) {
    $key = $object['Key'];
    $id = $object['ETag'];
    $cmd = $s3->getCommand('GetObject', [
        'Bucket' => $bucket,
        'Key'    => $key,
    ]);

    $res = gettype($cmd);
    //echo "<script type='text/javascript'>alert('$res');</script>";

    $request = $s3->createPresignedRequest($cmd, "+{$expire}");
    $signed_url = (string) $request->getUri();
    //$images[] = new ImageObject($signed_url, $id);
    /*$imgObj->setUrl($signed_url);
    $imgObj->setId($id);*/
    $imgObj = new ImageObject($signed_url, $id);

    //echo("<tr><td>$key</td><td><a href=\"{$bucket_url}/{$key}\">Direct</a></td><td><a href=\"{$signed_url}\">Expires in $expire</a></td></tr>");
    
    //this is the new one, not sure if it works
    //ideally you use this in a for loop that grabs each signed url and prints it out this through this echos
   //echo("<article class='card'><a href=""><figure><img src=\"{$signed_url}\"></figure></a></article>");
    // echo "<script type='text/javascript'>alert('$signed_url');</script>";

    echo '<article class="card"><a href="imageDisplay.php?key='.$key.'&id='.$id.'><figure><img src="'.$signed_url.'"</figure></a></article>';
}


/*foreach ($images as $object) {
  $url = $object->getUrl();
  $message = "wrong answer";
  echo "<script type='text/javascript'>alert('$message');</script>";
  echo("<article class='card'><a href=\"testDisplay.php\"><figure><img src=\"{$url}\"></figure></a></article>");
}*/
?>
    </section>
</main>
</body>
</html>
