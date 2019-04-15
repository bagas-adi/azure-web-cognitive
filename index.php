<!DOCTYPE html>
    <html>
    <head>
        <title>Analyze Sample</title>
        <script src="jquery-3.3.1.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    </head>
    <body class="container">
        <h1>Analyze image:</h1>
    <h4>Click the <strong>Analyze image</strong> button to start analyze.</h4>
    <br><br>
<?php 
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=bagasap90;AccountKey=0rtBRyCS9TAJL3VuBD7Z8fleUubbG26hIw3SxKfWCAilygRs7ChYs6EC2jj4LnE80SSwfqU+qd3hsLHV48/iuw==";
// echo $connectionString;
// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

$fileToUpload = "business_meeting.jpg"; 
if (!isset($_GET["Cleanup"])) {
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions(); 
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

    // Set container metadata.
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");

      $containerName = "blockblobs".generateRandomString();

    try {
        // Create container.
        $blobClient->createContainer($containerName, $createContainerOptions);

        // Getting local file so that we can upload it to Azure
        $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
        fclose($myfile);
        
        # Upload file as a block blob
        echo "Uploading BlockBlob: ".PHP_EOL;
        echo $fileToUpload;
        echo "<br />";
        
        $content = fopen($fileToUpload, "r"); 
        //Upload blob
        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
        // header("Content-Type:image/jpeg");
        // header('Content-Length: "' . filesize($fileToUpload) . '"');
        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix("business_meeting");

        echo "These are the blobs present in the container: ";

        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
                echo $blob->getName().": ".$blob->getUrl()."<br />";
                
    // echo '<input type="text" class="" name="inputImage" id="inputImage"
    //     value="'.$blob->getUrl().'" />';
                ?>
 <img style='max-width: 300px' src="<?php echo $blob->getUrl(); ?>"/><br/> 
<h5>Image to analyze:</h5>
<div class="form-group">
  <label for="usr">Image URL:</label>
  <input type="text" style="max-width: 600px" class="form-control" name="inputImage" id="inputImage" value="<?php echo $blob->getUrl(); ?>" />
</div>
<div id="wrapper" style="width:1020px; ">
        <div id="jsonOutput" style="width:600px; ">
            Response:
            <br><br>
            <textarea id="responseTextArea" class="UIInput"
                      style="width:580px; height:400px;"></textarea>
        </div>
        <<!-- div id="imageDiv" style="width:420px; display:table-cell;">
            Source image:
            <br><br>
            <img id="sourceImage" width="400" />
        </div> -->
    </div>
                <?php
            }
        
            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());
        echo "<br />";

        // Get blob.
        echo "The file has been uploaded! "; 
        echo "<br /><br/><br/>";
    }
    catch(ServiceException $e){ 
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){ 
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
} 
else 
{

    try{
        // Delete container.
        echo "Deleting Container".PHP_EOL;
        echo $_GET["containerName"].PHP_EOL;
        echo "<br />";
        $blobClient->deleteContainer($_GET["containerName"]);
    }
    catch(ServiceException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}
?>

<center>
<form method="post" action="phpQS.php?Cleanup&containerName=<?php echo $containerName; ?>">
    <div class="btn btn-group">
    <button class="btn btn-danger" type="submit">Clean up the Blob</button>
    <button class="btn btn-primary" type="button" onclick="processImage()">Analyze image</button></div>
</form>
</center>

    
     
    <script type="text/javascript">
        function processImage() {  
            var subscriptionKey = "710eede9cc234728ad05eec3cca146bf"; 
            var uriBase =
                "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
     
            // Request parameters.
            var params = {
                "visualFeatures": "Categories,Description,Color",
                "details": "",
                "language": "en",
            };
     
            // Display the image.
            var sourceImageUrl = document.getElementById("inputImage").value;
            // document.querySelector("#sourceImage").src = sourceImageUrl;
     
            // Make the REST API call.
            $.ajax({
                url: uriBase + "?" + $.param(params),
     
                // Request headers.
                beforeSend: function(xhrObj){
                    xhrObj.setRequestHeader("Content-Type","application/json");
                    xhrObj.setRequestHeader(
                        "Ocp-Apim-Subscription-Key", subscriptionKey);
                },
     
                type: "POST",
     
                // Request body.
                data: '{"url": ' + '"' + sourceImageUrl + '"}',
            })
     
            .done(function(data) {
                // Show formatted JSON on webpage.
                $("#responseTextArea").val(JSON.stringify(data, null, 2));
            })
     
            .fail(function(jqXHR, textStatus, errorThrown) {
                // Display error message.
                var errorString = (errorThrown === "") ? "Error. " :
                    errorThrown + " (" + jqXHR.status + "): ";
                errorString += (jqXHR.responseText === "") ? "" :
                    jQuery.parseJSON(jqXHR.responseText).message;
                alert(errorString);
            });
        };
    </script>
     
    
    
    
    <br><br>
    
    </body>
    </html>