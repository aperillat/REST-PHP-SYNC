<?PHP
//Sript takes Project selection and then provids option to choose 2 sets to sync together.
//CSS and javascript are for look and feel.
//PHP files related to this are:
//sync.php
//getsets.php
//getitems.php
include "sync.php";
?>
<!DOCTYPE html>
<html>
	<head>
	<title>Jama Auto Sync</title>
	</head>
	<body>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
		<div id="container">
		  	<div id="header">
				<img id="logo" src="img/logo.png" />
		  	</div>
			<div id="dropdowns">
				<h2>Jama Auto Sync</h2>
				<section class="main">
					<div class="wrapper">
						<div id="projects" class="dropdown-wrapper" tabindex="1">
							<span id="project-name">Projects</span>
							<ul class="dropdown">
					    <?PHP
					    //Builds a list of all projects in the givin instance.
					    //Instance URL is specified in sync.php
					    $result_count = -1;
					    $start_index = 0;
				        while($result_count != 0) 
				    	{
    						$start_at = "startAt=" . $start_index;
							$results = getResults('projects?'.$start_at.'&'.$max_results);
							$projects = $results['data'];
							foreach($projects as $project) 
							{		
								if((strcmp($project['fields']['statusId'], 298) == 0) && !$project['isFolder'])
								{
									?><li id="<?PHP echo $project["id"]; ?>"><a href="#"><?PHP echo $project["fields"]["name"];?></a></li><?PHP
								}
							}
							$page_info = $projects['meta']['pageInfo'];
    						$start_index = $page_info['startIndex'] + $allowed_results;
    						$result_count = $page_info['resultCount'];
						}?>
							</ul>
						</div>
						<br>
						<!--Placeholder for set dropdowns-->
						<div class="cascade" id="sets"></div>
						<br><br><br>
						<!--Hidden until sets are chosen-->
						<div class="cascade" id="submit">
							<button class="cascade" id="button" onclick="ButtonClick()">Sync Now!</button>
						</div>
						<br><br><br>
						<!--Results of syncing sets todether. Hidden until submit is selected-->
						<div class="cascade" id="results"></div>
					</div>
				</section>
			</div>
		</div>
		<script type="text/javascript" src="js/jquery-1.9.0.min.js"></script>
		<script>
		$(document).ready(function()
		{
			$("#submit").hide();
			$("#sets").hide();
		});
		//When butten is selected, getitems.php is ran and the results div is populated
		//with the results of the attempted sync. This may take a while if the sets have
		//multiple levels of nested children and are large.
		function ButtonClick() 
		{
			var set1id = document.getElementById("set1").className;
		    var set2id = document.getElementById("set2").className;
       		if(set1id != "" && set2id != ""  && set1id != set2id)
       		{
       			$.ajax(
	 			{
	 				type: "POST",
	 				url: "getitems.php",
	 				data: "set1_id="+set1id+"&set2_id="+set2id,
	 				cache: false,
	 				beforeSend: function () 
	 				{ 
						$('#results').html('<img src="img/loader.gif" alt="" width="24" height="24">');
					},
	 				success: function(html) 
	 				{
	 					$("#results").html( html );
	 				}
	 			});
       		}
		}

		function DropDown(el) 
		{
		    this.projects = el;
		    this.placeholder = this.projects.children('span');
		    this.opts = this.projects.find('ul.dropdown > li');
		    this.val = '';
		    this.index = -1;
		    this.initEvents();
		}

		//Event listener for when a project or set selection is made.
		DropDown.prototype = {
		    initEvents : function() 
		    {
		        var obj = this;
		        obj.projects.on('click', function(event)
		        {
		            $(this).toggleClass('active');
		            return false;
		        });
		 
		        obj.opts.on('click',function()
		        {
		            var opt = $(this);
		            obj.val = opt.text();
		            obj.index = opt.index();
		            var selection_id = opt.prop("id");
		            obj.placeholder.text(obj.val);
		            var listId = obj.placeholder.prop("id");
		            //If event is a project selection, the project id is sent to getsets.php
		            //this returns HTML to populate the placeholder with two dropdowns
		            //containing all the sets in the selected project
		 			if(listId == "project-name")
		 			{
			 			$("#sets").html( "" );
			 			$("#sets").show();
			 			if (selection_id.length > 0 ) 
			 			{
				 			$.ajax(
				 			{
				 				type: "POST",
				 				url: "getsets.php",
				 				data: "project_id="+selection_id,
				 				cache: false,
				 				beforeSend: function () 
				 				{ 
									$('#sets').html('<img src="img/loader.gif" alt="" width="24" height="24">');
								},
				 				success: function(html) 
				 				{
				 					$("#sets").html( html );
				 				}
				 			});
			 			}
		 			}
		 			//If event is a set selection, once both unique sets are selected,
		 			//The submit button is made visible
		 			else if(listId != undefined && (listId == "set1" || listId == "set2"))
		            {
						var set1class = document.getElementById(listId);
						if(set1class != undefined)
						{
							set1class.className = selection_id;
						}
						var set1class = document.getElementById("set1").className;
		            	var set2class = document.getElementById("set2").className;
		           		if(set1class != "" && set2class != ""  && set1class != set2class)
		           		{
		           			$("#submit").fadeIn("slow");
		           		}
		           		else
		           		{
		           			$("#submit").fadeOut("slow");
		           		}
		            } 
		        });
		    },
		    getValue : function() {
		        return this.val;
		    },
		    getIndex : function() {
		        return this.index;
		    }
		}

		$(function() {

				var projects = new DropDown( $('#projects') );

				$(document).click(function() {
					$('.projects').removeClass('active');
				});

			});
		</script>
	</body>
</html>