<?php
	session_start();

	//set vars
	$url = "http://osc.test/html2canvas2";  //path to application
	$base_img_dir = 'images'; //folder to store the images in relative to this script
	$img_dir_prefix = date('Y-m-d') . '_'; //just to make it easy to automate the cleanup of all the images that get saved to the server
	!defined('DS') ? define('DS', '/') : '';

	//parse the url sent by the proxy function
	//TODO: scrub the input better
	$submitted_img_url = trim(htmlentities(urldecode($_GET['url'])));

	//TODO:catch cases where a filename isn't the last element eg. http://sub.domain.ext/page
	$basename = basename($submitted_img_url);

	//test file type
	//TODO: test for other cases that don't have a '.'
	$pos = strrpos($submitted_img_url, '.', -1);
	$ext = substr($submitted_img_url, $pos);

	//set a dir for this request
	function randomNumber()
	{
		return substr(sha1(rand()), 0, 15);
	}

	if (!isset($_SESSION['html2canvas_proxy_img_path']))
	{
		//prevent a loop just in case....
		$i = 0;
		do{
			$random = randomNumber();
			$i++;
			$i === 10 ? exit : '';
		}while (is_dir($base_img_dir .DS . $img_dir_prefix . $random)); //TODO: think this part though better

		$_SESSION['html2canvas_proxy_img_path'] =  $random;

	} else {
		$random = $_SESSION['html2canvas_proxy_img_path'];
	}

	$new_dir = $img_dir_prefix . $random; //prepends the working image directory with the image directory prefix

	is_dir($base_img_dir . DS . $new_dir) ? '' : mkdir($base_img_dir . DS . $new_dir, 0755);

	$file_path = $base_img_dir . DS . $new_dir . DS .  $basename;

	//save the image
	if (!copy($submitted_img_url, $file_path)) exit;

	$new_location = $url . DS . $file_path;

	header('Content-Type: application/javascript');

	echo  "{$_GET['callback']}(" . json_encode($new_location) . ")";