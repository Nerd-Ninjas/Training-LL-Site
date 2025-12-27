<?php
require_once("includes/config.php");

$fileLink=$_POST['fileLink'];
$username=$_POST['username'];
$tool_id=$_POST['id'];
$marks=$_POST['totalMarks'];


if(isset($fileLink) && isset($username) && isset($tool_id) && isset($marks))
{	
 	$query = $con->prepare("SELECT * FROM tools_assignment_filelink 
                            WHERE username=:username AND tool_id=:tool_id" );
    $query->bindValue(":username", $username);
    $query->bindValue(":tool_id", $tool_id);
	$query->execute();

    if($query->rowCount() == 0) {
        $bookmark=1;
        $query = $con->prepare("INSERT INTO tools_assignment_filelink (username, tool_id, fileLink, totalMarks)
                                VALUES(:username, :tool_id, :fileLink, :totalMarks)");
        $query->bindValue(":username", $username);
    	$query->bindValue(":tool_id", $tool_id);
		$query->bindValue(":fileLink", $fileLink);
		$query->bindValue(":totalMarks", $marks);
	    $query->execute();
		if($query){
		echo "File submitted Succesfully!";	
		}
        
    }else{
		echo "File already submitted!";

	}		
	
}

else {
    echo "Data Missing!";
}

?>
