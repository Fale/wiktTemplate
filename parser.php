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
$content = $matches;
