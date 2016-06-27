<?php

/**
 * Retrieves Jive community data for a given site 
 * and returns it (along with schema) for a Tableau Web Data Connector
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

//constants
define('API_PROTECTION_STRING',"throw 'allowIllegalResourceCall is false.';");
define('API_PATH',"api/core/v3/");
define('TYPE_SCHEMA',"schema");
define('TYPE_DATA',"data");
define('TYPE_PLACES',"places");
define('BLOG_TABLE','blog');
define('ITERATION_LIMIT',10);

//custom functions
function determineDataType($value)
{
	if(is_null($value))
	{
		$dataType = "string";
	}
	elseif(is_bool($value))
	{
		$dataType = "bool";
	}
	elseif(is_numeric($value))
	{
		$numericValue = $value + 0; //returns either a float or int
		if(is_float($numericValue))
		{
			$dataType = "float";
		}
		else
		{
			$dataType = "int";
		}
	}
	elseif(strtotime($value))
	{
		$dataType = "datetime";
	}
	else
	{
		$dataType = "string";
	}
	
	return $dataType;
}

function makeRequest($url,$username,$password)
{
	error_log("url=" . $url);
	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($handle, CURLOPT_USERPWD, $username . ":" . $password); 
	$status_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);   //get status code
	$response = curl_exec($handle);
	curl_close($handle);
	
	//remove the protection string
	$resultsString = str_replace(API_PROTECTION_STRING,"",$response);
	
	//decode the JSON
	$results = json_decode($resultsString,true);
	
	return $results;
}

//get request vars
if(empty($_POST['type']))
{
	die("ERROR: Please specify whether you need the schema or the data");
}
elseif(empty($_POST['site']))
{
	die("ERROR: Please specify the Jive site");
}
elseif(empty($_POST['username']))
{
	die("ERROR: Please specify the username for the Jive site");
}
elseif(empty($_POST['password']))
{
	die("ERROR: Please specify the password for the Jive site");
}
else
{
	$type = $_POST['type'];
	$site = $_POST['site'];
	$username = $_POST['username'];
	$password = $_POST['password'];
}

//generate the specified output
$output = "";
if($type == TYPE_SCHEMA)
{
	//local vars
	$tables = array();
	
	//hard-code schemas for now
	//blog
	$columns = [["id" => "attachments", "alias"=>"Attachments", "dataType" => "string"],
	            ["id" => "author", "alias"=>"Author", "dataType" => "string"],
	            ["id" => "categories", "alias"=>"Categories", "dataType" => "string"],
	            ["id" => "content", "alias"=>"Content", "dataType" => "string"],
	            ["id" => "contentID", "alias"=>"Content ID", "dataType" => "int"],
	            ["id" => "favoriteCount", "alias"=>"Favorites", "dataType" => "int"],
	            ["id" => "followerCount", "alias"=>"Followers", "dataType"=>"int"],
	            ["id" => "iconCss", "alias"=>"Icon CSS", "dataType"=>"string"],
	            ["id" => "id", "alias"=>"ID", "dataType"=>"int"],
	            ["id" => "lastActivity", "alias"=>"Last Activity", "dataType"=>"datetime"],
	            ["id" => "likeCount", "alias"=>"Likes", "dataType"=>"int"],
	            ["id" => "parent", "alias"=>"Parent", "dataType"=>"string"],
	            ["id" => "parentContentVisible", "alias"=>"Parent Content Visible", "dataType"=>"bool"],
	            ["id" => "parentPlace", "alias"=>"Parent Place", "dataType"=>"string"],
	            ["id" => "parentVisible", "alias"=>"Parent Visible", "dataType"=>"bool"],
	            ["id" => "permalink", "alias"=>"Permanent Link", "dataType"=>"string"],
	            ["id" => "publishDate", "alias"=>"Publish Date", "dataType"=>"date"],
	            ["id" => "published", "alias"=>"Published", "dataType"=>"datetime"],
	            ["id" => "replyCount", "alias"=>"Replies", "dataType"=>"int"],
	            ["id" => "resources", "alias"=>"Resources", "dataType"=>"string"],
	            ["id" => "restrictReplies", "alias"=>"Restrict Replies", "dataType"=>"bool"],
	            ["id" => "status", "alias"=>"Status", "dataType"=>"string"],
	            ["id" => "subject", "alias"=>"Subject", "dataType"=>"string"],
	            ["id" => "tags", "alias"=>"Tags", "dataType"=>"string"],
	            ["id" => "type", "alias"=>"Type", "dataType"=>"string"],
	            ["id" => "updated", "alias"=>"Updated", "dataType"=>"datetime"],
	            ["id" => "viewCount", "alias"=>"Views", "dataType"=>"int"],
	            ["id" => "visibleToExternalContributors", "alias"=>"Visible to external contributors", "dataType"=>"bool"]];
	$tableInfo = ["id" => "blog",
	              "alias" => "Blog Posts",
	              "columns" => $columns];
	$tables[] = $tableInfo;
	
	//discussion
	$columns = [["id" => "attachments", "alias"=>"Attachments", "dataType" => "string"],
	            ["id" => "author", "alias"=>"Author", "dataType" => "string"],
	            ["id" => "categories", "alias"=>"Categories", "dataType" => "string"],
	            ["id" => "content", "alias"=>"Content", "dataType" => "string"],
	            ["id" => "contentID", "alias"=>"Content ID", "dataType" => "int"],
	            ["id" => "favoriteCount", "alias"=>"Favorites", "dataType" => "int"],
	            ["id" => "followerCount", "alias"=>"Followers", "dataType"=>"int"],
	            ["id" => "iconCss", "alias"=>"Icon CSS", "dataType"=>"string"],
	            ["id" => "id", "alias"=>"ID", "dataType"=>"int"],
	            ["id" => "lastActivity", "alias"=>"Last Activity", "dataType"=>"datetime"],
	            ["id" => "likeCount", "alias"=>"Likes", "dataType"=>"int"],
	            ["id" => "parent", "alias"=>"Parent", "dataType"=>"string"],
	            ["id" => "parentContentVisible", "alias"=>"Parent Content Visible", "dataType"=>"bool"],
	            ["id" => "parentPlace", "alias"=>"Parent Place", "dataType"=>"string"],
	            ["id" => "parentVisible", "alias"=>"Parent Visible", "dataType"=>"bool"],
	            ["id" => "published", "alias"=>"Published", "dataType"=>"datetime"],
	            ["id" => "question", "alias"=>"Question", "dataType"=>"bool"],
	            ["id" => "replyCount", "alias"=>"Replies", "dataType"=>"int"],
	            ["id" => "resolved", "alias"=>"Resolved", "dataType"=>"string"],
	            ["id" => "resources", "alias"=>"Resources", "dataType"=>"string"],
	            ["id" => "restrictReplies", "alias"=>"Restrict Replies", "dataType"=>"bool"],
	            ["id" => "sameQuestionCount", "alias"=>"Same Question Count", "dataType"=>"int"],
	            ["id" => "status", "alias"=>"Status", "dataType"=>"string"],
	            ["id" => "subject", "alias"=>"Subject", "dataType"=>"string"],
	            ["id" => "tags", "alias"=>"Tags", "dataType"=>"string"],
	            ["id" => "type", "alias"=>"Type", "dataType"=>"string"],
	            ["id" => "updated", "alias"=>"Updated", "dataType"=>"datetime"],
	            ["id" => "viewCount", "alias"=>"Views", "dataType"=>"int"],
	            ["id" => "visibility", "alias"=>"Visibility", "dataType"=>"string"],
	            ["id" => "visibleToExternalContributors", "alias"=>"Visible to external contributors", "dataType"=>"bool"]];
	$tableInfo = ["id" => "discussion",
	              "alias" => "Discussion Items",
	              "columns" => $columns];
	$tables[] = $tableInfo;
	
	//documents
	$columns = [["id" => "attachments", "alias"=>"Attachments", "dataType" => "string"],
	            ["id" => "author", "alias"=>"Author", "dataType" => "string"],
	            ["id" => "authors", "alias"=>"Authors", "dataType" => "string"],
	            ["id" => "authorship", "alias"=>"Authorship", "dataType" => "string"],
	            ["id" => "categories", "alias"=>"Categories", "dataType" => "string"],
	            ["id" => "content", "alias"=>"Content", "dataType" => "string"],
	            ["id" => "contentID", "alias"=>"Content ID", "dataType" => "int"],
	            ["id" => "contentImages", "alias"=>"Content Images", "dataType" => "string"],
	            ["id" => "favoriteCount", "alias"=>"Favorites", "dataType" => "int"],
	            ["id" => "followerCount", "alias"=>"Followers", "dataType"=>"int"],
	            ["id" => "iconCss", "alias"=>"Icon CSS", "dataType"=>"string"],
	            ["id" => "id", "alias"=>"ID", "dataType"=>"int"],
	            ["id" => "lastActivity", "alias"=>"Last Activity", "dataType"=>"datetime"],
	            ["id" => "likeCount", "alias"=>"Likes", "dataType"=>"int"],
	            ["id" => "outcomeCounts", "alias"=>"Outcomes", "dataType"=>"string"],
	            ["id" => "parent", "alias"=>"Parent", "dataType"=>"string"],
	            ["id" => "parentContentVisible", "alias"=>"Parent Content Visible", "dataType"=>"bool"],
	            ["id" => "parentPlace", "alias"=>"Parent Place", "dataType"=>"string"],
	            ["id" => "parentVisible", "alias"=>"Parent Visible", "dataType"=>"bool"],
	            ["id" => "published", "alias"=>"Published", "dataType"=>"datetime"],
	            ["id" => "replyCount", "alias"=>"Replies", "dataType"=>"int"],
	            ["id" => "resources", "alias"=>"Resources", "dataType"=>"string"],
	            ["id" => "restrictComments", "alias"=>"Restrict Replies", "dataType"=>"bool"],
	            ["id" => "status", "alias"=>"Status", "dataType"=>"string"],
	            ["id" => "subject", "alias"=>"Subject", "dataType"=>"string"],
	            ["id" => "tags", "alias"=>"Tags", "dataType"=>"string"],
	            ["id" => "type", "alias"=>"Type", "dataType"=>"string"],
	            ["id" => "updated", "alias"=>"Updated", "dataType"=>"datetime"],
	            ["id" => "viewCount", "alias"=>"Views", "dataType"=>"int"],
	            ["id" => "visibility", "alias"=>"Visibility", "dataType"=>"string"],
	            ["id" => "visibleToExternalContributors", "alias"=>"Visible to external contributors", "dataType"=>"bool"]];
	$tableInfo = ["id" => "document",
	              "alias" => "Documents",
	              "columns" => $columns];
	$tables[] = $tableInfo;
	
	//files
	$columns = [["id" => "author", "alias"=>"Author", "dataType" => "string"],
	            ["id" => "authorship", "alias"=>"Authorship", "dataType" => "string"],
	            ["id" => "binaryURL", "alias"=>"Binary URL", "dataType" => "string"],
	            ["id" => "categories", "alias"=>"Categories", "dataType" => "string"],
	            ["id" => "content", "alias"=>"Content", "dataType" => "string"],
	            ["id" => "contentID", "alias"=>"Content ID", "dataType" => "int"],
	            ["id" => "contentType", "alias"=>"Content Type", "dataType" => "string"],
	            ["id" => "favoriteCount", "alias"=>"Favorites", "dataType" => "int"],
	            ["id" => "followerCount", "alias"=>"Followers", "dataType"=>"int"],
	            ["id" => "iconCss", "alias"=>"Icon CSS", "dataType"=>"string"],
	            ["id" => "id", "alias"=>"ID", "dataType"=>"int"],
	            ["id" => "lastActivity", "alias"=>"Last Activity", "dataType"=>"datetime"],
	            ["id" => "likeCount", "alias"=>"Likes", "dataType"=>"int"],
	            ["id" => "name", "alias"=>"Name", "dataType"=>"string"],
	            ["id" => "parent", "alias"=>"Parent", "dataType"=>"string"],
	            ["id" => "parentContentVisible", "alias"=>"Parent Content Visible", "dataType"=>"bool"],
	            ["id" => "parentPlace", "alias"=>"Parent Place", "dataType"=>"string"],
	            ["id" => "parentVisible", "alias"=>"Parent Visible", "dataType"=>"bool"],
	            ["id" => "published", "alias"=>"Published", "dataType"=>"datetime"],
	            ["id" => "replyCount", "alias"=>"Replies", "dataType"=>"int"],
	            ["id" => "resources", "alias"=>"Resources", "dataType"=>"string"],
	            ["id" => "restrictComments", "alias"=>"Restrict Comments", "dataType"=>"bool"],
	            ["id" => "size", "alias"=>"Size", "dataType"=>"int"],
	            ["id" => "status", "alias"=>"Status", "dataType"=>"string"],
	            ["id" => "subject", "alias"=>"Subject", "dataType"=>"string"],
	            ["id" => "tags", "alias"=>"Tags", "dataType"=>"string"],
	            ["id" => "type", "alias"=>"Type", "dataType"=>"string"],
	            ["id" => "updated", "alias"=>"Updated", "dataType"=>"datetime"],
	            ["id" => "updater", "alias"=>"Updater", "dataType"=>"string"],
	            ["id" => "viewCount", "alias"=>"Views", "dataType"=>"int"],
	            ["id" => "visibility", "alias"=>"Visibility", "dataType"=>"string"],
	            ["id" => "visibleToExternalContributors", "alias"=>"Visible to external contributors", "dataType"=>"bool"]];
	$tableInfo = ["id" => "file",
	              "alias" => "Files",
	              "columns" => $columns];
	$tables[] = $tableInfo;
	
	//idea
	$columns = [["id" => "author", "alias"=>"Author", "dataType" => "string"],
	            ["id" => "authors", "alias"=>"Authors", "dataType" => "string"],
	            ["id" => "authorship", "alias"=>"Authorship", "dataType" => "string"],
	            ["id" => "authorshipPolicy", "alias"=>"Authorship Policy", "dataType" => "string"],
	            ["id" => "categories", "alias"=>"Categories", "dataType" => "string"],
	            ["id" => "commentCount", "alias"=>"Comments", "dataType" => "int"],
	            ["id" => "content", "alias"=>"Content", "dataType" => "string"],
	            ["id" => "contentID", "alias"=>"Content ID", "dataType" => "int"],
	            ["id" => "favoriteCount", "alias"=>"Favorites", "dataType" => "int"],
	            ["id" => "followerCount", "alias"=>"Followers", "dataType"=>"int"],
	            ["id" => "iconCss", "alias"=>"Icon CSS", "dataType"=>"string"],
	            ["id" => "id", "alias"=>"ID", "dataType"=>"int"],
	            ["id" => "lastActivity", "alias"=>"Last Activity", "dataType"=>"datetime"],
	            ["id" => "parent", "alias"=>"Parent", "dataType"=>"string"],
	            ["id" => "parentContentVisible", "alias"=>"Parent Content Visible", "dataType"=>"bool"],
	            ["id" => "parentPlace", "alias"=>"Parent Place", "dataType"=>"string"],
	            ["id" => "parentVisible", "alias"=>"Parent Visible", "dataType"=>"bool"],
	            ["id" => "published", "alias"=>"Published", "dataType"=>"datetime"],
	            ["id" => "replyCount", "alias"=>"Replies", "dataType"=>"int"],
	            ["id" => "resources", "alias"=>"Resources", "dataType"=>"string"],
	            ["id" => "score", "alias"=>"Score", "dataType"=>"int"],
	            ["id" => "stage", "alias"=>"Stage", "dataType"=>"string"],
	            ["id" => "status", "alias"=>"Status", "dataType"=>"string"],
	            ["id" => "subject", "alias"=>"Subject", "dataType"=>"string"],
	            ["id" => "tags", "alias"=>"Tags", "dataType"=>"string"],
	            ["id" => "type", "alias"=>"Type", "dataType"=>"string"],
	            ["id" => "updated", "alias"=>"Updated", "dataType"=>"datetime"],
	            ["id" => "viewCount", "alias"=>"Views", "dataType"=>"int"],
	            ["id" => "visibility", "alias"=>"Visibility", "dataType"=>"string"],
	            ["id" => "visibleToExternalContributors", "alias"=>"Visible to external contributors", "dataType"=>"bool"],
	            ["id" => "voteCount", "alias"=>"Votes", "dataType"=>"int"],
	            ["id" => "voted", "alias"=>"Voted", "dataType"=>"bool"]];
	$tableInfo = ["id" => "idea",
	              "alias" => "Ideas",
	              "columns" => $columns];
	$tables[] = $tableInfo;
	
	//poll
	$columns = [["id"=>"author", "alias"=>"Author", "dataType"=>"string"],
	            ["id"=>"categories", "alias"=>"Categories", "dataType"=>"string"],
	            ["id"=>"content", "alias"=>"Content", "dataType"=>"string"],
	            ["id"=>"contentID", "alias"=>"Content ID", "dataType"=>"int"],
	            ["id"=>"favoriteCount", "alias"=>"Favorites", "dataType"=>"int"],
	            ["id"=>"followerCount", "alias"=>"Followers", "dataType"=>"int"],
	            ["id"=>"iconCss", "alias"=>"Icon CSS", "dataType"=>"string"],
	            ["id"=>"id", "alias"=>"ID", "dataType"=>"int"],
	            ["id"=>"lastActivity", "alias"=>"Last Activity", "dataType"=>"datetime"],
	            ["id"=>"likeCount", "alias"=>"Likes", "dataType"=>"int"],
	            ["id"=>"options", "alias"=>"Options", "dataType"=>"string"],
	            ["id"=>"optionsImages", "alias"=>"Options Images", "dataType"=>"string"],
	            ["id"=>"parent", "alias"=>"Parent", "dataType"=>"string"],
	            ["id"=>"parentContentVisible", "alias"=>"Parent Content Visible", "dataType"=>"bool"],
	            ["id"=>"parentPlace", "alias"=>"Parent Place", "dataType"=>"string"],
	            ["id"=>"parentVisible", "alias"=>"Parent Visible", "dataType"=>"bool"],
	            ["id"=>"published", "alias"=>"Published", "dataType"=>"datetime"],
	            ["id"=>"replyCount", "alias"=>"Replies", "dataType"=>"int"],
	            ["id"=>"resources", "alias"=>"Resources", "dataType"=>"string"],
	            ["id"=>"startDate", "alias"=>"Start Date", "dataType"=>"datetime"],
	            ["id" => "status", "alias"=>"Status", "dataType"=>"string"],
	            ["id" => "subject", "alias"=>"Subject", "dataType"=>"string"],
	            ["id" => "tags", "alias"=>"Tags", "dataType"=>"string"],
	            ["id" => "type", "alias"=>"Type", "dataType"=>"string"],
	            ["id" => "updated", "alias"=>"Updated", "dataType"=>"datetime"],
	            ["id" => "viewCount", "alias"=>"Views", "dataType"=>"int"],
	            ["id" => "visibility", "alias"=>"Visibility", "dataType"=>"string"],
	            ["id" => "visibleToExternalContributors", "alias"=>"Visible to external contributors", "dataType"=>"bool"],
	            ["id"=>"voteCount", "alias"=>"Votes", "dataType"=>"int"],
	            ["id"=>"voteDates", "alias"=>"Vote Dates", "dataType"=>"string"],
	            ["id"=>"votes", "alias"=>"Vote Entries", "dataType"=>"string"]];
	$tableInfo = ["id" => "poll",
	              "alias" => "Polls",
	              "columns" => $columns];
	$tables[] = $tableInfo;
	
	$output = json_encode($tables);
}
elseif($type == TYPE_DATA)
{
	//local vars
	$data = array();
	$separator = (substr($site,-1) == '/') ? "" : '/';
	
	//get table name to determine how the URL
	$tableName = $_POST['table'];
	if($tableName == BLOG_TABLE)
	{
		//get the place again to determine blog link
		$placeURL = $site . $separator . API_PATH . "places/" . $_POST['place'];
		$results = makeRequest($placeURL,$username,$password);
		
		//get the blog
		$blogURL = $results['resources']['blog']['ref'];
		$results = makeRequest($blogURL,$username,$password);
		
		//get the blog contents url
		$url = $results['resources']['contents']['ref'] . "?count=100";
	}
	else
	{
		//get additional request vars
		$url = $site . $separator . API_PATH . "places/" . $_POST['place'] . "/contents?count=100&filter=type($tableName)";
	}
	
	//handle pagination
	$iteration = 0;
	do
	{
		$results = makeRequest($url,$username,$password);
		
		//get the data for the specified table
		foreach($results['list'] as $row)
		{
			$record = array();
			foreach($row as $element)
			{
				if(is_array($element))
				{
					$record[] = implode(",",$element);
				}
				else
				{
					$record[] = $element;
				}
			}
			
			$data[] = $record;
		}
		
		//check if more results exist
		if(array_key_exists("links",$results) && array_key_exists("next",$results['links']))
		{
			$url = $results['links']['next'];
		}
		else
		{
			$url = "";
		}
		
		//iterate
		$iteration += 1;
	} while ($url != "" && $iteration <= ITERATION_LIMIT);
	
	$output = json_encode($data);
}
elseif($type == TYPE_PLACES)
{
	//local vars
	$places = array();
	
	//build the request URL
	$separator = (substr($site,-1) == '/') ? "" : '/';
	$url = $site . $separator . API_PATH . "places?count=100";
	
	//handle pagination
	do
	{
		//make the request using basic HTTP authentication
		$results = makeRequest($url,$username,$password);
		
		//store the places
		foreach($results['list'] as $place)
		{
			$places[] = $place;
		}
		
		//check if more results exist
		if(array_key_exists("links",$results) && array_key_exists("next",$results['links']))
		{
			$url = $results['links']['next'];
		}
		else
		{
			$url = "";
		}
	} while ($url != "");
	$output = json_encode($places);
}
else
{
	die("ERROR: Unknown type ($type).  Please specify schema, data, or places.");
}
echo $output;
?>
