#!/usr/bin/php
<?php

require_once('class.geoplanet.php');
require_once('cliargs.php');

// You need to get your own API key from  http://developer.yahoo.com/geo/
define('YAHOO_API_KEY', '');

if (YAHOO_API_KEY==='')
    die("You have to get your own API key from  http://developer.yahoo.com/geo/ before you can use this script\n");

ini_set('memory_limit', '-1');

function get_place_from_location($geoplanet, $location)
{
    $placelist = $geoplanet->getPlaces($location);

    if (count($placelist)<1)
    {
        error_log("Place not found for $location");
        return null;
    }

	$topplace = $placelist[0];
	$country = $topplace['country'];
	$state = $topplace['admin1'];
	$county = $topplace['admin2'];

	$centroid = $topplace['centroid'];
    $lat = $centroid['lat'];
    $lon = $centroid['lng'];
    
    $result = array(
        'lat' => $lat,
        'lon' => $lon,
        'country' => $country,
        'state' => $state,
        'county' => $county,
    );

    return $result;
}

$geoplanet = new GeoPlanet(YAHOO_API_KEY);

$cliargs = array(
	'infilename' => array(
		'short' => 'i',
		'type' => 'optional',
		'description' => 'The file to pull the location data from',
        'default' => 'php://stdin',        
	),
	'outfilename' => array(
		'short' => 'o',
		'type' => 'optional',
		'description' => 'The file to write the output location data to - if unset, will write to stdout',
        'default' => 'php://stdout',
	),
    'showinput' => array(
        'short' => 's',
        'type' => 'switch',
        'description' => 'Whether to include the input string followed by a tab as the prefix to each line of results',
    ),
);	

$options = cliargs_get_options($cliargs);
$infilename = $options['infilename'];
$outfilename = $options['outfilename'];
$showinput = $options['showinput'];

$infilehandle = fopen($infilename, "r") or die("Couldn't open $infilename\n");
$outfilehandle = fopen($outfilename, "w") or die("Couldn't open $outfilename\n");

while(!feof($infilehandle))
{
    $currentline = fgets($infilehandle);
    $currentline = trim($currentline);
    
    if ($showinput)
        $result = $currentline."\t";
    else
        $result = '';
    
    $place = get_place_from_location($geoplanet, $currentline);
    if (empty($place))
        $result .= "-1000,-1000\n";
    else
        $result .= $place['lat'].",".$place['lon']."\n";
        
    fwrite($outfilehandle, $result);
}

fclose($infilehandle);
fclose($outfilehandle);

?>