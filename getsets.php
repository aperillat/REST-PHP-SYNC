<?PHP
include "sync.php"; 
//Project Id that has been selected
$project = trim(mysql_escape_string($_POST["project_id"]));
//retreivs all sets in the selected project and then returns two html lists containing the set information.
?>
<div class="dropdown-wrapper" id="set1-wrapper" float: left;>
	<span id="set1">Set 1</span>
	<ul class="dropdown">
		<?PHP
		$result_count = -1;
        $start_index = 0;
        $dump2 = array();
        while($result_count != 0) 
    	{
    		$start_at = "startAt=" . $start_index;
			$results = getResults('items?project='.$project.'&'.$start_at.'&'.$max_results);
			$data = $results['data'];
			foreach($data as $item) 
			{		
				if(isset($item['itemType']) && $item['itemType'] == 31)
				{
					?><li id="<?PHP echo $item['id']; ?>"><a href="#"><?PHP echo $item['fields']['name'];?></a></li><?PHP
					$results2[$item['id']] = $item['fields']['name'];
				}
			}
			$page_info = $results['meta']['pageInfo'];
    		$start_index = $page_info['startIndex'] + $allowed_results;
    		$result_count = $page_info['resultCount'];
		}?>
	</ul>
</div>
<div class="dropdown-wrapper" id="set2-wrapper" float: right;>
	<span id="set2">Set 2</span>
	<ul class="dropdown">
		<?PHP
		foreach($results2 as $key => $item) 
		{		
			if(isset($item))
			{
				?><li id="<?PHP echo $key; ?>"><a href="#"><?PHP echo $item;?></a></li><?PHP
			}
		}
		?>
	</ul>
</div>
<script>
$(function() 
{
		var set1 = new DropDown( $('#set1-wrapper') );
		$(document).click(function() 
		{
			$('.set1-wrapper').removeClass('active');
		});
		var set2 = new DropDown( $('#set2-wrapper') );
		$(document).click(function() 
		{
			$('.set2-wrapper').removeClass('active');
		});
});
</script>