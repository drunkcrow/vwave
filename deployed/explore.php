<?php
    //This page displays the user's gallery
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
            <!--<h2 class="jumbotron-heading">Explore</h2>-->
            <?php
                if(isset($_GET['category']))
                {
                    echo '<h2 class="jumbotron-heading">Category: '.$_GET['category'].'</h2>';
                } 
                else 
                {
                    echo '<h2 class="jumbotron-heading">Explore</h2>';
                }

                echo '<br>';
                echo '<div class="btn-group">';

                //User's email address
                // $email = $_SESSION['userData']['email'];
                // $prefix = $email . "/";
                // $del = '/';
                // if(isset($_GET['category']))
                // {
                //     $prefix .= $_GET['category'];
                //     $del = '';
                //     echo '<a class="btn mr-2" style="background-color:#663399" href="explore.php">Back to Explore</a>';
                // } 
                
            ?>
           
           <div class="dropdown">
                <button class="btn dropdown-toggle mr-2" type="button" id="categoryDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Categories
                </button>
                <div class="dropdown-menu" aria-labelledby="categoryDropdown">
                <?php
                    $categories = array("Digital Art","Traditional Art","Photography","Comics","Collage","Drawing","Painting","Landscape","Sculpture","Typography","3D Art","Photomanipulation","Pixel Art","Text Art","Vector","Fan Art");
                    echo '<a class="dropdown-item" href="explore.php">Return</a>';
                    foreach($categories as $cat) {
                        echo '<a class="dropdown-item" href="explore.php?category='.$cat.'">'.$cat.'</a>';
                    }
                ?>
                </div>
            </div>
        </div>
        
    </section>
    <br>
    <div class="container">
        <div class="gallery" id="gallery">
            <?php
                $topQuery = "SELECT email, keyname, likes FROM images i
                            INNER JOIN users u ON i.id = u.id
                            WHERE private = '0'
                            ORDER BY likes desc, i.created desc";
                $topResult = $conn->query($topQuery);
                $numRows = $topResult->num_rows;

                if($numRows == 0) {
                    echo '<p>No Results</p>';
                }
                else 
                {
                    if(isset($_GET['category'])){
                      $catQuery = 'SELECT  u.email , i.keyname, c.category_name , i.likes
                              FROM images i
                              INNER JOIN users u 
                                ON i.id = u.id
                                AND private = "0"
                              INNER JOIN categories c
                                ON i.keyname = c.keyname
                                AND c.category_name = "'.$_GET['category'].'"';
                      $topResult = $conn->query($catQuery);
                    }
                    //Iterate over each image to display them
                    while($image = $topResult->fetch_assoc()) {
                        //Get the images key (filename)
                        $key = $image['email'] . '/' . $image['keyname'];
                        
                        $s3Client = new S3Access();
                        $url = $s3Client->get($region, $bucket, $key);
                    
                        //Display each image as a link to the image display page 
                        echo '<div class="mb-3">';
                        echo '<a href="imageDisplay.php?key='.$key.'&exp=true"><img class="img-fluid" src="'.$url.'"></a>';
                        echo '</div>';
                    }
                }
            ?>
        </div>
    </div>
</main>
</body>
</html>
