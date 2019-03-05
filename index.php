<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.css" />

<style>
	.gallery img {
   /* width: 20%;*/
    height: 130px;
    border-radius: 5px;
    cursor: pointer;
    transition: .3s;
}
</style>
</head>
<body>
<div class="container">

    	<?php
    	  require "config.php";
    	   ?>

         <div class="row" style="margin-bottom: 40px;">
          <form action="newindex.php" method="post" enctype="multipart/form-data">
          Select Image Files to Upload:
          <input type="file" name="files[]" multiple >
          <input type="submit" name="submit" class="btn-primary" value="Upload">
      </form>
    </div>

      	<div class="gallery">
      		<div class="row">
    <?php
    global $conn;
		 $result = mysqli_query($conn, "SELECT * FROM images WHERE status = 1 ORDER BY uploaded_on DESC");

		 	while($row= mysqli_fetch_assoc($result)):

		 	$imageThumbURL = 'images/thumb/'.$row['file_name'];
		 	 $imageURL = 'images/'.$row['file_name'];
		 	?>

			<div class="col-md-2" style="margin-bottom: 10px;">
			    <div class="img-rounded">
			      <a data-fancybox="gallery" href="<?php echo $imageURL;?>">
			        <img src="<?php echo $imageThumbURL;?>" alt="Lights" style="width:100%">
			      </a>
			    </div>
		  </div>


		 	<?php
		 endwhile;

    ?>
      </div>
		</div>

    
</div>

</body>


  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.js"></script>

</html>

<?php

if(isset($_POST['submit'])){

    include_once 'config.php';
    global $conn;
    
    // File upload configuration
    $targetDir = "images/";
    $thumbnailDir = "images/thumb/";
    $allowTypes = array('jpg','png','jpeg','gif');
    
    $statusMsg = $errorMsg = $insertValuesSQL = $errorUpload = $errorUploadType = '';
    if(!empty(array_filter($_FILES['files']['name']))){
        foreach($_FILES['files']['name'] as $key=>$val){
            // File upload path
            $fileName = basename($_FILES['files']['name'][$key]);
            $targetFilePath = $targetDir . $fileName;
            $targetThumbnailPath = $thumbnailDir . $fileName;
            
            // Check whether file type is valid
            $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
            if(in_array($fileType, $allowTypes)){
                // Upload file to server and copy to thumbnail dir
                copy($_FILES["files"]["tmp_name"][$key], $targetThumbnailPath);
                if(move_uploaded_file($_FILES["files"]["tmp_name"][$key], $targetFilePath)){
                    // Image db insert sql
                    $insertValuesSQL .= "('".$fileName."','UploadFromSite',NOW()),";
                }else{
                    $errorUpload .= $_FILES['files']['name'][$key].', ';
                }
            }else{
                $errorUploadType .= $_FILES['files']['name'][$key].', ';
            }
        }
        
        if(!empty($insertValuesSQL)){
            $insertValuesSQL = trim($insertValuesSQL,',');
            // Insert image file name into database
            $insert = mysqli_query($conn, "INSERT INTO images (file_name, title, uploaded_on) VALUES $insertValuesSQL");


            if (!$insert)
            {
               $statusMsg = "Sorry, there was an error uploading your file.";
            }
            else{
                $errorUpload = !empty($errorUpload)?'Upload Error: '.$errorUpload:'';
                $errorUploadType = !empty($errorUploadType)?'File Type Error: '.$errorUploadType:'';
                $errorMsg = !empty($errorUpload)?'<br/>'.$errorUpload.'<br/>'.$errorUploadType:'<br/>'.$errorUploadType;
                $statusMsg = "Files are uploaded successfully.".$errorMsg;
            }
        }
    }else{
        $statusMsg = 'Please select a file to upload.';
    }
    
    echo $statusMsg;
    header('location: newindex.php');
}
?>