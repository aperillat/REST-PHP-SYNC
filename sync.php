<?PHP
$configs = include('config.php');
$allowed_results = 20;
$max_results = "maxResults=" . $allowed_results;
//getResults returns the entire json resopnse as an array
function getResults($url)
{
    $data =  CallAPI('GET', $url);
    $array = json_decode($data, true);
    return $array;
}

//CallAPI executes the curl REST call and returns the results as a json object
//The server URL is hard coded below and the path of the request is appended onto the end
//Username and Password are hard coded too. For better security, place url and auth in a 
//separate file outside pubilc relm.
function CallAPI($method, $path, $data = false)
{
    global $configs;
	  $url = $configs['server'].$path;
    $curl = curl_init();
    $headers = array(
        'Content-type: application/json',
    );
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $configs['username'].":".$configs['password']);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);           
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

function createNewItem($parent_id, $project_id, $itemType, $child_type, $content, $parent_is_project = false)
{
   $payload = '{"project":'.$project_id.',
   "itemType":'.$itemType.',
   "childItemType":'.$child_type.',
   "location":{"parent":{';
   if($parent_is_project)
    $payload = $payload.'"project":'.$project_id.'}}';
   else
    $payload = $payload.'"item":'.$parent_id.'}}';
   $payload = $payload.',"fields":'.$content.'}';
   $result = CallAPI('POST', 'items', $payload);
   $array = json_decode($result, true);
   $id = $array['meta']['location'];
   return substr($id, strrpos($id, '/') + 1);
}
?>