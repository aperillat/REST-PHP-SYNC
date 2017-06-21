<?PHP
include "sync.php"; 
//IDs of the two sets being synced
$first = trim(mysql_escape_string($_POST["set1_id"]));
$second = trim(mysql_escape_string($_POST["set2_id"]));
//Sync the two top level sets first
CallAPI('POST', 'items/'.$first.'/synceditems', '{"item":'.$second.'}');
$match = false;
$result_count = -1;
$start_index = 0;
while($result_count != 0) 
{
	$start_at = "startAt=" . $start_index;
	$set1_children = getResults('items/'.$first.'/children?'.$start_at.'&'.$max_results);
	$set2_children = getResults('items/'.$second.'/children?'.$start_at.'&'.$max_results);
	//get children results for reach set. This is the first level of children. We use recursion
	//to get sub children until we are at the end. This is done using the 'totalResults' flag
	//in the JSON response.
	getChildren($set1_children, $set2_children);
	//recursive function that takes the ID of two items and compares their names. If they match
	//they are synced together. 
	//The recusion happens if both items have children. In this case, all the children IDs are
	//also passed to the function using recursion to itterate through all children of a set.
	//This returns html responses for each sync that occurs.
	$page_info = $set1_children['meta']['pageInfo'];
	$start_index = $page_info['startIndex'] + $allowed_results;
	$result_count = $page_info['resultCount'];
}
if(!$match)
{
    ?><label>Neither sets had any matching items</label><br><?PHP
}

function getChildren($s1, $s2)
{
	global $match;
	$s1_data = $s1['data'];
	$s2_data = $s2['data'];
	foreach ($s1_data as $item1)
	{
		foreach ($s2_data as $item2)
		{
			if(isset($item1['fields']['name']) && isset($item2['fields']['name']) && (strcmp($item1['fields']['name'],$item2['fields']['name']) == 0))
			{
				$jsonResponse = CallAPI('POST', 'items/'.$item1['id'].'/synceditems', '{"item":'.$item2['id'].'}');
				$response = json_decode($jsonResponse, true);
				$status = $response['meta']['status'];
				$message = $response['meta']['message'];
	            $match = true;
				if(isset($status) && strcmp($status,"Created") == 0)
	            {
	                ?><label>sync status: <?PHP echo $status . ", " . "id1 = ".$item1['id'] . ", id2 = ".$item2['id'];?></label><br><?PHP
	                $match = true;
	            }
	            elseif(isset($message))
	            {
	            	?><label>sync status: <?PHP echo $message . ", " . "id1 = ".$item1['id'] . ", id2 = ".$item2['id'];?></label><br><?PHP
	            }
				else
	            {
					?><label>something went wrong with '<?PHP echo $item1['fields']['name']; ?>'</label><br><?PHP
	            }
			}
			if(isset($item1['fields']['name']) && isset($item2['fields']['name']))
			{
				$s1_results = getResults('items/'.$item1['id'].'/children?'.$max_results);
				$page_info = $s1_results['meta']['pageInfo'];
				$result_count = $page_info['resultCount'];
				if($result_count > 0)
				{
					$s2_results = getResults('items/'.$item2['id'].'/children?'.$max_results);
					$page_info = $s2_results['meta']['pageInfo'];
					$result_count = $page_info['resultCount'];
					if($result_count > 0)
					{
						getChildren($s1_results, $s2_results);
					}
				}
			}
		}
		
    }
}
?>