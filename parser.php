<?php

/**
 * @author Fabio Alessandro Locati <fabiolocati@gmail.com>
 * @copyright Fabio Alessandro Locati 2013
 * @license AGPL-3.0 http://www.gnu.org/licenses/agpl-3.0.html
 */

// Declare stuff
include( 'botclasses.php' );
$wiki      = new wikipedia;
$wiki->url = "http://it.wiktionary.org/w/api.php";

// Download the page and retrive only text between <syntaxhighlight> tag
// TODO: Handle pages with more than one <syntaxhighlight> tag
$page = $wiki->getpage("Wikizionario:Bot/Sostituzioni/Template");
preg_match_all("/<syntaxhighlight.*>(.*)<\/syntaxhighlight>/ims", $page, $matches);
$content = $matches[1][0];

preg_match_all("/'\\\{\\\{(.*)'/iU", $content, $matches);
$clean = Array();
$jump = 0;
foreach ($matches[1] as $match)
{
	// Remove escaping, pipes and spaces
	$match = str_replace("\|", "", $match);
	$match = str_replace("\-", "-", $match);
	$match = str_replace(" ", "_", $match);

	// Look for (text|text) regex
	if (preg_match_all("/\(/i", $match, $dummy))
	{
		preg_match_all("/(.*)\((.*)\)(.*)/i", $match, $m);
		foreach (explode("|", $m[2][0]) as $char) {
			array_push($clean, $m[1][0] . $char . $m[3][0]);
		}
		$jump = 1;
	}
	// Look for [Tt] regex
	// TODO: Make possible to have more than once of these
	if (preg_match_all("/\[/i", $match, $dummy))
	{
		preg_match_all("/(.*)\[(.*)\](.*)/i", $match, $m);
		foreach (str_split($m[2][0]) as $char) {
			array_push($clean, $m[1][0] . $char . $m[3][0]);
		}
		$jump = 1;
	}
	if ($jump == 0)
		array_push($clean, $match);
	else
		$jump = 0;
}

// Prepare the query and save in a file
$q  = " CONNECT itwiktionary_p itwiktionary.labsdb;
  SELECT DISTINCT concat(\"# [[\", page.page_title, \"]]\")
  FROM page JOIN templatelinks ON page.page_id = templatelinks.tl_from
  WHERE templatelinks.tl_title IN ('" . implode("', '", $clean) . "')
    AND page.page_namespace = 0
  ORDER BY page.page_title;";
file_put_contents("query.sql", $q);

// Run the query
shell_exec("mysql --defaults-file=~/replica.my.cnf -h itwiktionary.labsdb -BN < query.sql > output.out");
