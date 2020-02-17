<?php
include 'header.php';
include 'queries.php';

if($_SESSION['login'] != TRUE) {
    header('Location: index.php');
}
$expire = "1 hour";
date_default_timezone_set("UTC");

$emailCompare = $_GET['searchQ'];
        
$query0 = $selectRecipientSender_Friendrequests_Recipient;
$queryF = $selectAll_Friends_User;
$queryB = $selectAll_Blocked_User;

$Cnt = 0;

?>
<main role="main">
    <div class="container">
        <br>
        <div class="jumbotron">
            <h2 class="jumbotron-heading">Your Friends</h2>
        </div>
        <div class="friends">
            <?php
                if ($resultF = $conn->query($queryF)) 
                {
                /* fetch associative array */
                    while ($rowF = $resultF->fetch_assoc()) 
                    {
                        //$friendsQuery = $selectFriendDetails_Innerjoin_Users;
                        $friendsQuery = "SELECT nickname, email, picture FROM users u INNER join usernames n on u.id = n.id WHERE u.id = '" . $rowF["friend"] . "'";
                        if($friendsRes = $conn->query($friendsQuery)) 
                        {
                            $friendsRow = $friendsRes->fetch_assoc();
                            //echo "<p>" . $rowFF["nickname"] . ", </p>";
                            echo '<div style="margin-bottom:.3em">';
                            echo '<img src="'.$friendsRow['picture'].'" class="img-thumbnail">';
                            echo "<a href = 'searchPage.php?searchQ=" . $friendsRow["email"] . "'>" . $friendsRow["nickname"] . "</a>";
                            echo '</div>';
                        //echo "<a href = 'searchPage.php?searchQ=" . $row["email"] . "'>" . $row["nickname"] . "</a>";
                        }
                    }
                }

                /* free result set */
                //$result->free();
            ?>
        </div>
        <br>
        <div class="jumbotron">
            <h2 class="jumbotron-heading">Your Friends Requests</h2>
        </div>
        <div class="friends">
        <?php
            if ($result = $conn->query($query0)) {
                while ($row = $result->fetch_assoc()) {
                    $query1 = "SELECT nickname, picture FROM usernames n inner join users u on n.id = u.id where n.id = '".$row["sender"]. "'";
                    if($result1 = $conn->query($query1)) {
                        $row1 = $result1->fetch_assoc();
                        echo '<div style="margin-bottom:.3em">';
                        echo '<img style="display:inline-block" src="'.$row1['picture'].'" class="img-thumbnail">';
                        echo "<p style='display:inline-block'>" . $row1["nickname"]. "</p>";
                        echo '<form style="display:inline-block" action = "accept.php" method="get"><input type="hidden" name="accept" value='.$row["sender"].'></input> <button class="btn" type="submit">Accept</button></form>';
                        echo '</div>';
                    }
                }

              /* free result set */
              $result->free();
}
?>
</div>
    <br>
    <div class="jumbotron">
        <h2 class="jumbotron-heading">Your Blocked List</h2>
    </div>
    <div class="friends">
<?php
if ($resultB = $conn->query($queryB)) 
{
    while ($rowB = $resultB->fetch_assoc()) 
    {
        $queryU = "SELECT nickname, email, picture FROM usernames n inner join users u on n.id = u.id where n.id ='".$rowB["blocked_user"]."'";
        if($resultU = $conn->query($queryU)) 
        {
            $rowU = $resultU->fetch_assoc();
            echo '<div style="margin-bottom:.3em">';
            echo '<img style="display:inline-block" src="'.$rowU['picture'].'" class="img-thumbnail">';
            echo "<a href = 'searchPage.php?searchQ=" . $rowU["email"] . "'>" . $rowU["nickname"] . "</a>";
            echo '</div>';
        }
    }
}
    /* free result set */
$resultU->free();
?>
</div>

    </container>
</main>
</body>
</html>
