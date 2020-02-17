<?php
include 'header.php';
require_once 's3Access.php';

//Check if user is logged in, if not redirect to index
if($_SESSION['login'] != TRUE) {
    header('Location: index.php');
    exit();
}

//Requires
date_default_timezone_set("UTC");
require './vendor/autoload.php';
?>

<main role="main">
<br>
<section class="jumbotron text-center" style="color:rebeccapurple">
<div class="container">

<?php
if(isset($_GET['gal']))
{
  echo '<h2 class="jumbotron-heading">'.$_GET['gal'].'</h2>';
} else if (isset($_GET['list'])){
  echo '<h2 class="jumbotron-heading">'.$_GET['list'].'</h2>';
}else {
  echo '<h2 class="jumbotron-heading">Your Uploads</h2>';
}

echo '<br>';
echo '<div class="btn-group">';

//User's email address
$email = $_SESSION['userData']['email'];
$prefix = $email . "/";
$del = '/';
if(isset($_GET['gal']))
{
  $prefix .= $_GET['gal'];
  $del = '';
  echo '<a class="btn mr-2" style="background-color:#663399" href="deleteGallery.php?prefix='.$prefix.'&gal='.$_GET['gal'].'">Delete Gallery</a>';
} else if (isset($_GET['list'])) {
  $prefix .= $_GET['list'];
  $del = '';
  echo '<a class="btn mr-2" style="background-color:#663399" href="deleteList.php?prefix='.$prefix.'&list='.$_GET['list'].'">Delete List</a>';
} else {
  ?>
  <a><button type="button" class="btn mr-2" id="galBtn" data-toggle="modal" data-target="#galModal">Create A New Gallery</button></a>

  <!-- Modal -->
  <div class="modal fade" id="galModal" tabindex="-1" role="dialog" aria-labelledby="galModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
      <div class="modal-header">
          <h5 class="modal-title" id="galModalLongTitle">Create A New Gallery</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span style="color:white" aria-hidden="true">&times;</span>
          </button>
      </div>
      <div class="modal-body">
          <form action="create_gallery.php" method="post" id="galForm">
              <div class="form-group">
                  <input style="font-family:Tinos" type="text" class="form-control" id="name" name="name" placeholder="Name" required>
              </div>
          </form>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <input type="submit" form="galForm" class="btn" />
      </div>
      </div>
  </div>
  </div>
<?php
}
?>
</div>
<div class="btn-group">
  <div class="dropdown">
    <button class="btn dropdown-toggle mr-2" type="button" id="galDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Galleries
    </button>
    <div class="dropdown-menu" aria-labelledby="galDropdown">
      <a class="dropdown-item" href="home.php">Uploads</a>
      <?php
          foreach($_SESSION['galleries'] as $gal) {
            echo '<a class="dropdown-item" href="home.php?gal='.$gal.'">'.$gal.'</a>';
          }
      ?>
    </div>
  </div>

  <div class="dropdown">
    <button class="btn dropdown-toggle mr-2" type="button" id="galDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Lists
    </button>
    <div class="dropdown-menu" aria-labelledby="galDropdown">
      <a><button type="button" class="btn mr-2" id="listBtn" data-toggle="modal" data-target="#listModal">Create A New List</button></a>
      <?php
          foreach($_SESSION['lists'] as $list) {
            echo '<a class="dropdown-item" href="home.php?list='.$list.'">'.$list.'</a>';
          }
      ?>
    </div>
  </div>
</div>

<div class="modal fade" id="listModal" tabindex="-1" role="dialog" aria-labelledby="listModalTitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="listModalLongTitle">Create A New List</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span style="color:white" aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <form action="create_list.php" method="post" id="listForm">
            <div class="form-group">
                <input style="font-family:Tinos" type="text" class="form-control" id="name" name="name" placeholder="Name" required>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <input type="submit" form="listForm" class="btn" />
    </div>
    </div>
</div>
</div>

</div>
</section>

<div class="container">
<div class="gallery" id="gallery">

<?php
//Start a new AWS S3Client
$s3 = new Aws\S3\S3Client([
  'version' => '2006-03-01',
  'region'  => $region,
]);

if(isset($_GET['list'])) {
  $listQuery = "SELECT * FROM `list_items` WHERE `user_id` = '".$_SESSION['userData']['id']."' AND `list` = '".$_GET['list']."'";
  $listRes = $conn->query($listQuery);
  while($listRow = $listRes->fetch_assoc()) {
    $key = $listRow['creator'] . '/' . $listRow['keyname'];
    $s3Client = new S3Access();
    $url = $s3Client->get($region, $bucket, $key);
      
    //Display each image as a link to the image display page 
    echo '<div class="mb-3">';
    echo '<a href="imageDisplay.php?key='.$key.'&list='.$list.'"><img class="img-fluid" src="'.$url.'"></a>';
  }
} else {
  //Get iterator for user's folder in S3 to get all images
  $iterator = $s3->getIterator('ListObjects', array('Bucket' => $bucket, 'Prefix' => $prefix, 'Delimiter' => $del));

  //Iterate over each image to display them
  foreach ($iterator as $object) {
      //Get the images key (filename), and etag
      $key = $object['Key'];
      $id = $object['ETag'];
      //This command gets the image from S3 as presigned url
      $s3Client = new S3Access();
      $url = $s3Client->get($region, $bucket, $key);
      
      //Display each image as a link to the image display page 
      echo '<div class="mb-3">';
      echo '<a href="imageDisplay.php?key='.$key.'"><img class="img-fluid" src="'.$url.'"></a>';
      echo '</div>';
  }
}
?>
</div>
</div>
</main>

<script>
  // Get the modal
  var modal = document.getElementById('galModal');
  // Get the button that opens the modal
  var btn = document.getElementById("galBtn");
  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];
  // When the user clicks the button, open the modal 
  btn.onclick = function() {
      modal.style.display = "block";
  }
  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
      modal.style.display = "none";
  }
  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
      if (event.target == modal) {
          modal.style.display = "none";
      }
  }
  $("#name").alphanum({
   allowSpace: true,
   allowNewline: false,
   allowOtherCharSets: false,
   allowNumeric: false
   });
</script>
<<<<<<< HEAD
<?php 
  include 'footer.php' 
?>;
=======

<?php
  include 'footer.php';
?>

>>>>>>> Development
</body>
</html>
