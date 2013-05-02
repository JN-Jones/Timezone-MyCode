<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("parse_message", "time_mycode");
$plugins->add_hook("parse_message_quote", "time_mycode");

function time_info()
{
	return array(
		"name"			=> "Time MyCode",
		"description"	=> "Adds a timezone MyCode to your Board",
		"website"		=> "http://jonesboard.de/",
		"author"		=> "Jones",
		"authorsite"	=> "http://jonesboard.de/",
		"version"		=> "1.0",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}

function time_activate() {}

function time_deactivate() {}

function time_mycode($message)
{
	$message = preg_replace_callback("#\[time=([a-zA-Z0-9\s+-.:]*)\](.*?)\[/time\]#si", "time_create", $message);
	$message = preg_replace_callback("#\[time\](.*?)\[/time\]#si", "time_create_user", $message);

	return $message;
}

function time_create(array $match)
{
	global $mybb;
	
	$zone = $match[1];
	$time = $match[2];

	if(strpos($zone, "GMT") !== false)
	    $offset = trim(substr($zone, 3));
	else
		$offset = $zone;
	
	if(substr($offset, -3) == ":30")
	    $offset = substr($offset, 0, -3).".5";
	elseif(substr($offset, -3) == ":00")
	    $offset = substr($offset, 0, -3);

	//Probably GMT?
	if(!is_numeric($offset))
	    $offset = 0;
		
	if(isset($mybb->user['uid']) && $mybb->user['uid'] != 0 && array_key_exists("timezone", $mybb->user))
	{
		$moffset = $mybb->user['timezone'];
		$dstcorrection = $mybb->user['dst'];
	}
	else
	{
		$moffset = $mybb->settings['timezoneoffset'];
		$dstcorrection = $mybb->settings['dstcorrection'];
	}

	// If DST correction is enabled, add an additional hour to the timezone.

	if($dstcorrection == 1)
	{
		++$moffset;
		if(my_substr($moffset, 0, 1) != "-")
		{
			$moffset = "+".$moffset;
		}
	}
	
	$tz = date_default_timezone_get();
	date_default_timezone_set("GMT");
	$stamp = strtotime($time);
	date_default_timezone_set($tz);

	if($stamp === false)
	    //Error, just return the normal time
		return $time;

	//$stamp = $stamp;// - ($moffset*3600);

	//Generate GMT Time
	$gmtstamp = $stamp - ($offset*3600);

	$format = $mybb->settings['timeformat'];
	if(!empty($mybb->user['timeformat']))
	    $format = $mybb->user['timeformat'];
	
	$ntime = my_date($format, $gmtstamp);

	return $ntime;
}

function time_create_user(array $match)
{
	global $mybb, $post;

	$time = $match[1];

	$user = $post['user'];

	if(isset($user['uid']) && $user['uid'] != 0 && array_key_exists("timezone", $user))
	{
		$offset = $user['timezone'];
		$dstcorrection = $user['dst'];
	}
	else
	{
		$offset = $mybb->settings['timezoneoffset'];
		$dstcorrection = $mybb->settings['dstcorrection'];
	}

	// If DST correction is enabled, add an additional hour to the timezone.

	if($dstcorrection == 1)
	{
		++$offset;
		if(my_substr($offset, 0, 1) != "-")
		{
			$offset = "+".$offset;
		}
	}

	if(isset($mybb->user['uid']) && $mybb->user['uid'] != 0 && array_key_exists("timezone", $mybb->user))
	{
		$moffset = $mybb->user['timezone'];
		$dstcorrection = $mybb->user['dst'];
	}
	else
	{
		$moffset = $mybb->settings['timezoneoffset'];
		$dstcorrection = $mybb->settings['dstcorrection'];
	}

	// If DST correction is enabled, add an additional hour to the timezone.

	if($dstcorrection == 1)
	{
		++$moffset;
		if(my_substr($moffset, 0, 1) != "-")
		{
			$moffset = "+".$moffset;
		}
	}

	$tz = date_default_timezone_get();
	date_default_timezone_set("GMT");
	$stamp = strtotime($time);
	date_default_timezone_set($tz);

	if($stamp === false)
	    //Error, just return the normal time
		return $time;

	//Generate GMT Time
	$gmtstamp = $stamp - ($offset * 3600);

	$format = $mybb->settings['timeformat'];
	if(!empty($mybb->user['timeformat']))
	    $format = $mybb->user['timeformat'];

	$ntime = my_date($format, $gmtstamp);


	return $ntime;
}
?>